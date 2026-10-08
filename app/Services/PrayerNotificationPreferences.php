<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPrayerNotificationSetting;

/**
 * Per-customer prayer notification settings. Only configured prayers have rows;
 * the rest fall back to config('prayer_times.notifications.defaults').
 */
class PrayerNotificationPreferences
{
    public const FIELDS = ['enabled', 'sound_enabled', 'vibration_enabled'];

    /**
     * @return array<string, array{enabled: bool, sound_enabled: bool, vibration_enabled: bool}>
     */
    public function for(Customer $customer): array
    {
        $saved = $customer->relationLoaded('prayerNotificationSettings')
            ? $customer->prayerNotificationSettings
            : $customer->prayerNotificationSettings()->get();

        $saved = $saved->keyBy('prayer');
        $settings = [];

        foreach (PrayerTimesService::TIMES as $prayer) {
            $row = $saved->get($prayer);

            foreach (self::FIELDS as $field) {
                $settings[$prayer][$field] = $row
                    ? (bool) $row->{$field}
                    : (bool) config("prayer_times.notifications.defaults.{$prayer}.{$field}", true);
            }
        }

        return $settings;
    }

    /**
     * Applies a partial payload: only the given prayers and fields change.
     *
     * @param  array<string, array<string, bool>>  $changes
     * @return array<string, array{enabled: bool, sound_enabled: bool, vibration_enabled: bool}>
     */
    public function update(Customer $customer, array $changes): array
    {
        $current = $this->for($customer);
        $rows = [];

        foreach (PrayerTimesService::TIMES as $prayer) {
            if (! isset($changes[$prayer]) || ! is_array($changes[$prayer])) {
                continue;
            }

            $values = [...$current[$prayer], ...array_intersect_key($changes[$prayer], array_flip(self::FIELDS))];

            $rows[] = [
                'customer_id' => $customer->id,
                'prayer' => $prayer,
                'enabled' => (bool) $values['enabled'],
                'sound_enabled' => (bool) $values['sound_enabled'],
                'vibration_enabled' => (bool) $values['vibration_enabled'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            CustomerPrayerNotificationSetting::query()->upsert(
                $rows,
                ['customer_id', 'prayer'],
                [...self::FIELDS, 'updated_at'],
            );
        }

        $customer->unsetRelation('prayerNotificationSettings');

        return $this->for($customer);
    }
}
