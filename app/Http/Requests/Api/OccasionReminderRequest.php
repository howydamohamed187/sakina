<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create (POST) requires title and event_date; update (PUT/PATCH) accepts partial payloads.
 */
class OccasionReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('title'))) {
            $this->merge(['title' => trim($this->input('title'))]);
        }
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'title' => [$required, 'string', 'max:150'],
            'event_date' => [$required, 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:2100-12-31'],
            'dua_id' => [
                'nullable',
                'integer',
                Rule::exists('duas', 'id')->whereNull('deleted_at')->where('status', 'active'),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
