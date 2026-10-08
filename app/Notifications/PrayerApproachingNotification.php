<?php

namespace App\Notifications;

use Carbon\CarbonInterface;

/**
 * Sent prayer_times.notifications.before_minutes before an enabled prayer.
 */
class PrayerApproachingNotification extends CustomerPushNotification
{
    /** @var array<string, string> */
    private array $titles;

    /** @var array<string, string> */
    private array $bodies;

    /**
     * @param  array{enabled: bool, sound_enabled: bool, vibration_enabled: bool}  $settings
     */
    public function __construct(
        public string $prayer,
        public CarbonInterface $time,
        public int $minutes,
        public array $settings,
    ) {
        [$this->titles, $this->bodies] = static::translate('prayer_approaching', fn (string $locale): array => [
            'prayer' => __("api.prayers.{$prayer}", [], $locale),
            'minutes' => $minutes,
            'time' => $time->format('H:i'),
        ]);
    }

    public function key(): string
    {
        return 'prayer_approaching';
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
            'entity_type' => 'prayer',
            'entity_id' => $this->prayer,
            'prayer_time' => $this->time->toIso8601String(),
            'minutes_before' => $this->minutes,
        ];
    }

    public function pushData(): array
    {
        return [
            'sound_enabled' => $this->settings['sound_enabled'] ? 1 : 0,
            'vibration_enabled' => $this->settings['vibration_enabled'] ? 1 : 0,
        ];
    }
}
