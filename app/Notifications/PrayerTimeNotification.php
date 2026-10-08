<?php

namespace App\Notifications;

use App\Notifications\Channels\FirebaseChannel;
use Carbon\CarbonInterface;
use Illuminate\Notifications\Notification;

/**
 * Push-only (not stored in the in-app list: five per day would flood it). Sound and
 * vibration follow the customer's settings for this prayer; Android 8+ applies them per
 * channel, so the push targets one of: prayer_sound_vibrate, prayer_sound, prayer_vibrate, prayer_silent.
 */
class PrayerTimeNotification extends Notification
{
    /**
     * @param  array{enabled: bool, sound_enabled: bool, vibration_enabled: bool}  $settings
     */
    public function __construct(
        public string $prayer,
        public CarbonInterface $time,
        public array $settings,
    ) {}

    public function via(object $notifiable): array
    {
        return [FirebaseChannel::class];
    }

    /**
     * @return array{title: string, body: string, data: array<string, string>, sound: bool, vibration: bool, android_channel: string}
     */
    public function toFirebase(object $notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        $key = $this->prayer === 'sunrise' ? 'sunrise_time' : 'prayer_time';
        $replace = [
            'prayer' => __("api.prayers.{$this->prayer}", [], $locale),
            'time' => $this->time->format('H:i'),
        ];

        $sound = $this->settings['sound_enabled'];
        $vibration = $this->settings['vibration_enabled'];

        return [
            'title' => __("notifications.{$key}.title", $replace, $locale),
            'body' => __("notifications.{$key}.body", $replace, $locale),
            'sound' => $sound,
            'vibration' => $vibration,
            'android_channel' => self::androidChannel($sound, $vibration),
            'data' => [
                'key' => $key,
                'prayer' => $this->prayer,
                'time' => $this->time->toIso8601String(),
                'sound_enabled' => $sound ? '1' : '0',
                'vibration_enabled' => $vibration ? '1' : '0',
            ],
        ];
    }

    public static function androidChannel(bool $sound, bool $vibration): string
    {
        return match (true) {
            $sound && $vibration => 'prayer_sound_vibrate',
            $sound => 'prayer_sound',
            $vibration => 'prayer_vibrate',
            default => 'prayer_silent',
        };
    }
}
