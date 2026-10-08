<?php

namespace App\Http\Resources;

use App\Models\DailyQuestion;
use App\Models\DailyQuestionAnswer;
use App\Models\DailyQuestionAssignment;
use App\Models\DailyQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Before an answer exists nothing here reveals which option is correct:
 * no is_correct per option, no correct_answer, no explanation.
 *
 * @mixin DailyQuestion
 */
class DailyQuizResource extends JsonResource
{
    /**
     * @param  Collection<int, DailyQuestionOption>  $options
     */
    public function __construct(
        $resource,
        private readonly DailyQuestionAssignment $assignment,
        private readonly ?DailyQuestionAnswer $answer,
        private readonly Collection $options,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'question' => $this->body,
            'date' => $this->assignment->shown_on?->toDateString(),
            'answered' => $this->answer !== null,
            'answers' => $this->options
                ->map(fn (DailyQuestionOption $option): array => ['id' => $option->id, 'text' => $option->body])
                ->all(),
        ];

        if (! $this->answer) {
            return $data;
        }

        return $data + self::result($this->resource, $this->answer);
    }

    /**
     * @return array{is_correct: bool, result_message: string, selected_answer_id: ?int, correct_answer: ?array{id: int, text: string}, explanation: ?string}
     */
    public static function result(DailyQuestion $question, DailyQuestionAnswer $answer): array
    {
        $correct = $question->correctOption();

        return [
            'is_correct' => (bool) $answer->is_correct,
            'result_message' => __($answer->is_correct ? 'api.daily_quiz.correct' : 'api.daily_quiz.wrong'),
            'selected_answer_id' => $answer->daily_question_option_id,
            'correct_answer' => $correct ? ['id' => $correct->id, 'text' => $correct->body] : null,
            'explanation' => filled($question->explanation) ? $question->explanation : null,
        ];
    }
}
