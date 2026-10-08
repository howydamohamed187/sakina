<?php

namespace App\Notifications;

use App\Models\OccasionReminder;
use Carbon\CarbonInterface;

/**
 * Yearly occasion reminder, with the linked dua (if any) so Flutter can open it.
 */
class OccasionReminderNotification extends CustomerPushNotification
{
    /** @var array<string, string> */
    private array $titles;

    /** @var array<string, string> */
    private array $bodies;

    public function __construct(
        public OccasionReminder $reminder,
        public CarbonInterface $occurrence,
    ) {
        [$this->titles, $this->bodies] = static::translate(
            $reminder->dua ? 'occasion_reminder_with_dua' : 'occasion_reminder',
            ['title' => $reminder->title, 'dua' => $reminder->dua?->title ?? ''],
        );
    }

    public function key(): string
    {
        return 'occasion_reminder';
    }

    public function titles(): array
    {
        return $this->titles;
    }

    public function bodies(): array
    {
        return $this->bodies;
    }

    public function viewData(): array
    {
        return [
            'entity_type' => 'occasion_reminder',
            'entity_id' => $this->reminder->id,
            'occurrence_date' => $this->occurrence->toDateString(),
            'dua_id' => $this->reminder->dua_id,
        ];
    }
}
