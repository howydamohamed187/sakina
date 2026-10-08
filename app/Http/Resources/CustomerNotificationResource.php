<?php

namespace App\Http\Resources;

use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerNotificationResource extends JsonResource
{
    private const TEXT_KEYS = ['key', 'title', 'body', 'title_i18n', 'body_i18n', 'viewData'];

    public function toArray(Request $request): array
    {
        $payload = is_array($this->data) ? $this->data : [];
        $viewData = is_array($payload['viewData'] ?? null)
            ? $payload['viewData']
            : array_diff_key($payload, array_flip(self::TEXT_KEYS));

        return [
            'id' => $this->id,
            'key' => $payload['key'] ?? null,
            'title' => $this->localized($payload['title_i18n'] ?? $payload['title'] ?? null),
            'description' => $this->localized($payload['body_i18n'] ?? $payload['body'] ?? null),
            'entity_type' => $viewData['entity_type'] ?? null,
            'entity_id' => $viewData['entity_id'] ?? null,
            'reference_number' => $viewData['reference_number'] ?? null,
            'created_date' => $this->created_at?->toDateTimeString(),
            'formatted_date' => $this->created_at?->format('Y-m-d h:i a'),
            'data' => (object) $viewData,
            'read_at' => $this->read_at?->toDateTimeString(),
        ];
    }

    /**
     * Stored texts may be locale maps, double-encoded JSON strings, or a single string.
     *
     * @return array<string, string>
     */
    private function localized(mixed $value): array
    {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : array_fill_keys(Locales::all(), $value);
        }

        $value = is_array($value) ? $value : [];
        $fallback = (string) ($value[Locales::default()] ?? reset($value) ?: '');
        $texts = [];

        foreach (Locales::all() as $locale) {
            $texts[$locale] = (string) ($value[$locale] ?? $fallback);
        }

        return $texts;
    }
}
