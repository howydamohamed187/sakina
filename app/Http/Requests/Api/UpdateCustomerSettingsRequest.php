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

    protected function prepareForValidation(): void
    {
        $language = $this->input('preferred_language');

        if (! is_string($language)) {
            return;
        }

        $language = mb_strtolower(trim($language));

        $aliases = [
            'ku' => 'ckb',
            'kur' => 'ckb',
            'ckb' => 'ckb',
            'ku_iq' => 'ckb',
            'ku-iq' => 'ckb',
            'ckb_iq' => 'ckb',
            'ckb-iq' => 'ckb',
            'kurdish' => 'ckb',
            'sorani' => 'ckb',
            'كردي' => 'ckb',
            'کوردی' => 'ckb',
            'arabic' => 'ar',
            'عربي' => 'ar',
            'english' => 'en',
        ];

        $this->merge(['preferred_language' => $aliases[$language] ?? $language]);
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
