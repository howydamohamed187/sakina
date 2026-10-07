<?php

namespace App\Services\PrayerTimes\Providers;

use App\Services\PrayerTimes\Contracts\PrayerTimesProvider;
use App\Services\PrayerTimes\PrayerTimeCalculator;
use Carbon\CarbonImmutable;

class LocalPrayerTimesProvider implements PrayerTimesProvider
{
    public function __construct(private readonly PrayerTimeCalculator $calculator) {}

    public function name(): string
    {
        return 'local';
    }

    public function times(float $latitude, float $longitude, CarbonImmutable $day, string $timezone, string $method): array
    {
        $times = $this->calculator->calculate(
            latitude: $latitude,
            longitude: $longitude,
            year: $day->year,
            month: $day->month,
            day: $day->day,
            method: $method,
            asrMethod: (string) config('prayer_times.asr_method', 'standard'),
            highLatitudeRule: (string) config('prayer_times.high_latitude_rule', 'middle_of_night'),
            adjustments: (array) config('prayer_times.adjustments', []),
        );

        return array_map(fn (CarbonImmutable $utc): CarbonImmutable => $utc->setTimezone($timezone), $times);
    }
}
