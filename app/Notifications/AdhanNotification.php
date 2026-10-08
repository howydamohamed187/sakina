<?php

namespace App\Notifications;

use Carbon\CarbonInterface;

/**
 * Sent when an enabled prayer time arrives. sound_enabled / vibration_enabled come from
 * the customer's settings for that prayer so Flutter can play the adhan or stay silent.
 */
class AdhanNotification extends CustomerPushNotification
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
        public array $settings,
    ) {
        [$this->titles, $this->bodies] = static::translate($prayer === 'sunrise' ? 'sunrise_time' : 'adhan', fn (string $locale): array => [
            'prayer' => __("api.prayers.{$prayer}", [], $locale),
            'time' => $time->format('H:i'),
        ]);
    }

    public function key(): string
    {
        return 'adhan';
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
