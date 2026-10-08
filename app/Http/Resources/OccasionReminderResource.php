<?php

namespace App\Http\Resources;

use App\Models\OccasionReminder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OccasionReminder */
class OccasionReminderResource extends JsonResource
{
    public function __construct($resource, private readonly bool $detailed = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $next = $this->nextOccurrence();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'event_date' => $this->event_date->toDateString(),
            'next_reminder_date' => $next->toDateString(),
            'days_until' => (int) now()->startOfDay()->diffInDays($next, true),
            'is_active' => $this->is_active,
            'dua' => $this->dua ? [
                'id' => $this->dua->id,
                'name' => $this->dua->title,
                ...($this->detailed ? ['dua_text' => $this->dua->body] : []),
            ] : null,
        ];
    }
}
