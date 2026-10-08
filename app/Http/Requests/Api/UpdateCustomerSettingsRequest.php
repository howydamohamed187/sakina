<?php

namespace App\Http\Requests\Api;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'notification_status' => ['sometimes', 'required', 'boolean'],
            'preferred_language' => ['sometimes', 'required', 'string', Rule::in(Locales::all())],
        ];
    }
}
