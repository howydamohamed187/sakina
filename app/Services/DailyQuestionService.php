<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DailyQuestion;
use App\Models\DailyQuestionAnswer;
use App\Models\DailyQuestionAssignment;
use App\Models\DailyQuestionOption;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class DailyQuestionService
{
    /**
     * @return array{question: DailyQuestion, assignment: DailyQuestionAssignment, answer: ?DailyQuestionAnswer}
     */
    public function todayFor(Customer $customer, ?Carbon $day = null): array
    {
        $day ??= now();
        $shownOn = $day->toDateString();

        $assignment = DailyQuestionAssignment::query()
            ->with(['question.options', 'answer'])
            ->where('customer_id', $customer->id)
            ->whereDate('shown_on', $shownOn)
            ->first();

        if ($assignment?->question) {
            return [
                'question' => $assignment->question,
                'assignment' => $assignment,
                'answer' => $assignment->answer,
            ];
        }

        $question = $this->pickRandomUnseen($customer);

        if (! $question) {
            throw ValidationException::withMessages([
                'question' => __('api.daily_question_unavailable'),
            ]);
        }

        $assignment = DailyQuestionAssignment::query()->create([
            'daily_question_id' => $question->id,
            'customer_id' => $customer->id,
            'shown_on' => $shownOn,
            'cycle' => $this->nextCycle($customer, $question),
        ]);

        $assignment->setRelation('question', $question->load('options'));
        $assignment->setRelation('answer', null);

        return [
            'question' => $question,
            'assignment' => $assignment,
            'answer' => null,
        ];
    }

    public function answer(Customer $customer, int $optionId, ?Carbon $day = null): DailyQuestionAnswer
    {
        $payload = $this->todayFor($customer, $day);

        if ($payload['answer']) {
            throw ValidationException::withMessages([
                'option_id' => __('api.daily_question_already_answered'),
            ]);
        }

        /** @var DailyQuestionOption|null $option */
        $option = $payload['question']->options->firstWhere('id', $optionId);

        if (! $option) {
            throw ValidationException::withMessages([
                'option_id' => __('api.daily_question_invalid_option'),
            ]);
        }

        $answer = DailyQuestionAnswer::query()->create([
            'daily_question_id' => $payload['question']->id,
            'daily_question_option_id' => $option->id,
            'customer_id' => $customer->id,
            'daily_question_assignment_id' => $payload['assignment']->id,
            'option_body' => $option->body,
            'is_correct' => $option->is_correct,
            'answered_at' => now(),
        ]);

        $payload['assignment']->setRelation('answer', $answer);

        return $answer;
    }

    private function pickRandomUnseen(Customer $customer): ?DailyQuestion
    {
        $cycle = $this->currentCycle($customer);

        $seenIds = DailyQuestionAssignment::query()
            ->where('customer_id', $customer->id)
            ->where('cycle', $cycle)
            ->pluck('daily_question_id');

        $query = DailyQuestion::query()->active();

        $unseen = (clone $query)->whereNotIn('id', $seenIds);

        if ($unseen->exists()) {
            $lastId = DailyQuestionAssignment::query()
                ->where('customer_id', $customer->id)
                ->latest('id')
                ->value('daily_question_id');

            $avoidLast = (clone $unseen)->when(
                $lastId && $unseen->count() > 1,
                fn ($builder) => $builder->where('id', '!=', $lastId)
            );

            return $avoidLast->inRandomOrder()->first();
        }

        return $query->inRandomOrder()->first();
    }

    private function currentCycle(Customer $customer): int
    {
        return (int) (DailyQuestionAssignment::query()
            ->where('customer_id', $customer->id)
            ->max('cycle') ?: 1);
    }

    private function nextCycle(Customer $customer, DailyQuestion $question): int
    {
        $cycle = $this->currentCycle($customer);

        $seenInCycle = DailyQuestionAssignment::query()
            ->where('customer_id', $customer->id)
            ->where('cycle', $cycle)
            ->pluck('daily_question_id');

        $activeCount = DailyQuestion::query()->active()->count();

        if ($activeCount > 0 && $seenInCycle->count() >= $activeCount) {
            return $cycle + 1;
        }

        if ($seenInCycle->contains($question->id)) {
            return $cycle + 1;
        }

        return $cycle;
    }
}
