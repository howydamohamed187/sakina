<?php

namespace App\Notifications;

use App\Models\OccasionReminder;
use App\Notifications\Channels\FirebaseChannel;
use Carbon\CarbonInterface;

/**
 * Yearly occasion reminder: stored in the customer's in-app notifications (database)
 * and pushed through FCM to the customer's devices.
 */
class OccasionReminderNotification extends LocalizedNotification
{
    public function __construct(
        public OccasionReminder $reminder,
        public CarbonInterface $occurrence,
    ) {
        parent::__construct(
            $reminder->dua ? 'occasion_reminder_with_dua' : 'occasion_reminder',
            ['title' => $reminder->title, 'dua' => $reminder->dua?->title ?? ''],
        );
    }

    public function via(object $notifiable): array
    {
        return ['database', FirebaseChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            ...parent::toArray($notifiable),
            ...$this->payload(),
        ];
    }

    /**
     * @return array{title: string, body: string, data: array<string, string>}
     */
    public function toFirebase(object $notifiable): array
    {
        $data = parent::toArray($notifiable);

        return [
            'title' => $data['title'],
            'body' => $data['body'],
            'data' => array_map('strval', array_filter([
                'key' => $this->key,
                ...$this->payload(),
            ], fn ($value): bool => $value !== null)),
        ];
    }

    /**
     * @return array{reminder_id: int, occurrence_date: string, dua_id: int|null}
     */
    private function payload(): array
    {
        return [
            'reminder_id' => $this->reminder->id,
            'occurrence_date' => $this->occurrence->toDateString(),
            'dua_id' => $this->reminder->dua_id,
        ];
    }
}
