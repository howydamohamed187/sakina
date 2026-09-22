<?php

namespace App\Http\Resources;

use App\Models\DailyQuestion;
use App\Models\DailyQuestionAnswer;
use App\Models\DailyQuestionAssignment;
use App\Support\QuestionCategories;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DailyQuestion */
class DailyQuestionResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly ?DailyQuestionAssignment $assignment = null,
        private readonly ?DailyQuestionAnswer $answer = null,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $answered = $this->answer !== null;

        $answers = $this->options
            ->shuffle()
            ->values()
            ->map(fn ($option): array => [
                'id' => $option->id,
                'body' => $option->body,
                ...($answered ? ['is_correct' => (bool) $option->is_correct] : []),
            ]);

        return [
            'id' => $this->id,
            'body' => $this->body,
            'category' => $this->category,
            'category_label' => QuestionCategories::label($this->category),
            'shown_on' => $this->assignment?->shown_on?->toDateString(),
            'answered' => $answered ? 1 : 0,
            'selected_option_id' => $this->answer?->daily_question_option_id,
            'is_correct' => $answered ? (int) $this->answer->is_correct : null,
            'answers' => $answers,
        ];
    }
}
