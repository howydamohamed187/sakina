<?php

namespace App\Http\Resources;

use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];
        $key = $data['key'] ?? null;

        return [
            'id' => $this->id,
            'title' => $this->localizedText($data, 'title', $key ? "notifications.{$key}.title" : null),
            'body' => $this->localizedText($data, 'body', $key ? "notifications.{$key}.body" : null),
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function localizedText(array $data, string $field, ?string $fallbackKey): ?string
    {
        if (isset($data[$field.'_i18n'])) {
            return Locales::pick($data[$field.'_i18n']);
        }

        if (isset($data[$field])) {
            return Locales::pick($data[$field]);
        }

        return $fallbackKey ? __($fallbackKey) : null;
    }
}
