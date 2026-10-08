<?php

namespace App\Http\Requests\Api;

use App\Services\PrayerNotificationPreferences;
use App\Services\PrayerTimesService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePrayerNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->collect()->isEmpty() && ! $this->isJson()) {
            $decoded = json_decode((string) $this->getContent(), true);

            if (is_array($decoded)) {
                $this->merge($decoded);
            }
        }

        foreach (['prayers', 'settings', 'data'] as $wrapper) {
            if (is_array($this->input($wrapper)) && $this->collect()->only(PrayerTimesService::TIMES)->isEmpty()) {
                $this->merge($this->input($wrapper));

                return;
            }
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (PrayerTimesService::TIMES as $prayer) {
            $rules[$prayer] = ['sometimes', 'array'];

            foreach (PrayerNotificationPreferences::FIELDS as $field) {
                $rules["{$prayer}.{$field}"] = ['sometimes', 'boolean'];
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->collect()->only(PrayerTimesService::TIMES)->isEmpty()) {
                    $validator->errors()->add('prayers', __('api.prayer_notifications.empty'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (PrayerTimesService::TIMES as $prayer) {
            $name = __("api.prayers.{$prayer}");
            $attributes[$prayer] = $name;

            foreach (PrayerNotificationPreferences::FIELDS as $field) {
                $attributes["{$prayer}.{$field}"] = $name.' - '.__("api.prayer_notifications.fields.{$field}");
            }
        }

        return $attributes;
    }
}
