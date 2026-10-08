<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AnswerDailyQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Only the chosen option id is accepted; correctness is decided server-side.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'answer_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
