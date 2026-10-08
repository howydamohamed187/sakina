<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DailyQuestion;
use App\Models\DailyQuestionAnswer;
use App\Models\DailyQuestionAssignment;
use App\Models\DailyQuestionOption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * One question per customer per calendar day (app timezone). Each customer walks through
 * the active questions in a random order without repeats; when every active question has
 * been shown, a new cycle starts in a fresh random order.
 */
class DailyQuestionService
{
    /**
     * @return array{question: DailyQuestion, assignment: DailyQuestionAssignment, answer: ?DailyQuestionAnswer}
     */
    public function todayFor(Customer $customer, ?Carbon $day = null): array
    {
        $shownOn = $this->dayKey($day);

        $assignment = $this->assignmentFor($customer, $shownOn);

        if (! $assignment) {
            [$question, $cycle] = $this->pickNext($customer);

            if (! $question) {
                throw ValidationException::withMessages([
                    'question' => __('api.daily_question_unavailable'),
                ]);
            }

            DailyQuestionAssignment::query()->createOrFirst(
                ['customer_id' => $customer->id, 'shown_on' => $shownOn],
                ['daily_question_id' => $question->id, 'cycle' => $cycle],
            );

            $assignment = $this->assignmentFor($customer, $shownOn);
        }

        return [
            'question' => $assignment->question,
            'assignment' => $assignment,
            'answer' => $assignment->answer,
        ];
    }

    /**
     * Correctness always comes from the stored option, never from the client.
     */
    public function answer(Customer $customer, int $optionId, ?Carbon $day = null, string $field = 'option_id'): DailyQuestionAnswer
    {
        $payload = $this->todayFor($customer, $day);

        return DB::transaction(function () use ($payload, $customer, $optionId, $field): DailyQuestionAnswer {
            $assignment = DailyQuestionAssignment::query()
                ->whereKey($payload['assignment']->id)
                ->lockForUpdate()
                ->first();

            if (DailyQuestionAnswer::query()->where('daily_question_assignment_id', $assignment->id)->exists()) {
                throw ValidationException::withMessages([
                    $field => __('api.daily_question_already_answered'),
                ]);
            }

            /** @var DailyQuestionOption|null $option */
            $option = $payload['question']->options->firstWhere('id', $optionId);

            if (! $option) {
                throw ValidationException::withMessages([
                    $field => __('api.daily_question_invalid_option'),
                ]);
            }

            $answer = DailyQuestionAnswer::query()->create([
                'daily_question_id' => $payload['question']->id,
                'daily_question_option_id' => $option->id,
                'customer_id' => $customer->id,
                'daily_question_assignment_id' => $assignment->id,
                'option_body' => $option->body,
                'is_correct' => $option->is_correct,
                'answered_at' => now(),
            ]);

            $payload['assignment']->setRelation('answer', $answer);

            return $answer;
        });
    }

    /**
     * Same order for the same assignment (stable across app reopens), different per customer/day.
     *
     * @return Collection<int, DailyQuestionOption>
     */
    public static function shuffledOptions(DailyQuestion $question, ?DailyQuestionAssignment $assignment): Collection
    {
        if ($assignment === null) {
            return $question->options->values();
        }

        return collect((new Randomizer(new Mt19937($assignment->id)))->shuffleArray($question->options->all()));
    }

    /**
     * @return array{participations_count: int, correct_answers_count: int}
     */
    public function statsFor(Customer $customer): array
    {
        $stats = DailyQuestionAnswer::query()
            ->where('customer_id', $customer->id)
            ->selectRaw('COUNT(*) as participations, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as correct')
            ->first();

        return [
            'participations_count' => (int) ($stats?->participations ?? 0),
            'correct_answers_count' => (int) ($stats?->correct ?? 0),
        ];
    }

    /**
     * @return array{total_answered: int, correct_answers: int, wrong_answers: int, correct_percentage: int|float}
     */
    public function statistics(Customer $customer): array
    {
        $stats = $this->statsFor($customer);
        $total = $stats['participations_count'];
        $correct = $stats['correct_answers_count'];

        return [
            'total_answered' => $total,
            'correct_answers' => $correct,
            'wrong_answers' => $total - $correct,
            'correct_percentage' => $total === 0 ? 0 : round($correct / $total * 100, 1) + 0,
        ];
    }

    public function dayKey(?Carbon $day = null): string
    {
        return ($day ?? now())->copy()->setTimezone((string) config('app.timezone'))->toDateString();
    }

    private function assignmentFor(Customer $customer, string $shownOn): ?DailyQuestionAssignment
    {
        $assignment = DailyQuestionAssignment::query()
            ->with(['question' => fn ($query) => $query->withTrashed()->with('options'), 'answer'])
            ->where('customer_id', $customer->id)
            ->whereDate('shown_on', $shownOn)
            ->first();

        return $assignment?->question ? $assignment : null;
    }

    /**
     * @return array{0: ?DailyQuestion, 1: int}
     */
    private function pickNext(Customer $customer): array
    {
        $history = DailyQuestionAssignment::query()->where('customer_id', $customer->id);
        $cycle = (int) ((clone $history)->max('cycle') ?: 1);
        $seen = (clone $history)->where('cycle', $cycle)->pluck('daily_question_id');

        $question = DailyQuestion::query()->active()->whereNotIn('id', $seen)->inRandomOrder()->first();

        if ($question) {
            return [$question, $cycle];
        }

        $lastId = (clone $history)->latest('shown_on')->latest('id')->value('daily_question_id');
        $active = DailyQuestion::query()->active();

        $question = (clone $active)
            ->when($lastId && (clone $active)->count() > 1, fn ($query) => $query->whereKeyNot($lastId))
            ->inRandomOrder()
            ->first();

        return [$question, $cycle + 1];
    }
}
