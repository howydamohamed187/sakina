<?php

namespace App\Services;

use App\Services\PrayerTimes\PrayerTimeCalculator;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;

class PrayerTimesService
{
    public const TIMES = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];

    public const PRAYERS = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];

    /** @var array<string, array{0: float, 1: float}>|null */
    private static ?array $zoneLocations = null;

    public function __construct(private readonly PrayerTimeCalculator $calculator) {}

    /**
     * @return array<string, CarbonImmutable>
     */
    public function getTodayPrayerTimes(float $latitude, float $longitude, ?string $timezone = null): array
    {
        $timezone ??= $this->resolveTimezone($latitude, $longitude);

        return $this->getPrayerTimes($latitude, $longitude, CarbonImmutable::now($timezone), $timezone);
    }

    /**
     * @return array<string, CarbonImmutable>
     */
    public function getTomorrowPrayerTimes(float $latitude, float $longitude, ?string $timezone = null): array
    {
        $timezone ??= $this->resolveTimezone($latitude, $longitude);

        return $this->getPrayerTimes($latitude, $longitude, CarbonImmutable::now($timezone)->addDay(), $timezone);
    }

    /**
     * Prayer times (including sunrise) for the local calendar day of $date in $timezone.
     *
     * @return array<string, CarbonImmutable>
     */
    public function getPrayerTimes(float $latitude, float $longitude, CarbonInterface $date, ?string $timezone = null): array
    {
        $timezone ??= $this->resolveTimezone($latitude, $longitude);
        $day = CarbonImmutable::parse($date->format('Y-m-d'), $timezone);

        $key = implode(':', [
            'prayer_times',
            $day->toDateString(),
            round($latitude, 4),
            round($longitude, 4),
            $timezone,
            config('prayer_times.calculation_method'),
            config('prayer_times.asr_method'),
            config('prayer_times.high_latitude_rule'),
            md5(json_encode(config('prayer_times.adjustments', []))),
        ]);

        $times = Cache::remember(
            $key,
            (int) config('prayer_times.cache_ttl', 172800),
            fn () => $this->calculate($latitude, $longitude, $day, $timezone)
        );

        return array_map(
            fn (string $iso) => CarbonImmutable::parse($iso)->setTimezone($timezone),
            $times
        );
    }

    /**
     * @param  array<string, CarbonInterface>  $todayTimes
     * @param  array<string, CarbonInterface>  $tomorrowTimes
     * @return array{key: string, name: string, time: string, datetime: string, remaining_seconds: int}
     */
    public function getNextPrayer(array $todayTimes, array $tomorrowTimes, ?CarbonInterface $now = null): array
    {
        $now ??= CarbonImmutable::now();

        foreach (self::PRAYERS as $prayer) {
            if (isset($todayTimes[$prayer]) && $todayTimes[$prayer]->greaterThan($now)) {
                return $this->formatNextPrayer($prayer, $todayTimes[$prayer], $now);
            }
        }

        return $this->formatNextPrayer('fajr', $tomorrowTimes['fajr'], $now);
    }

    /**
     * @param  array<string, CarbonInterface>  $times
     * @return list<array{key: string, name: string, time: string}>
     */
    public function toList(array $times): array
    {
        return array_map(fn (string $key) => [
            'key' => $key,
            'name' => $this->name($key),
            'time' => $times[$key]->format('H:i'),
        ], self::TIMES);
    }

    public function name(string $key): string
    {
        return __("api.prayers.{$key}");
    }

    /**
     * Approximates the IANA timezone of a coordinate by picking the zone whose
     * reference location is closest to it.
     */
    public function resolveTimezone(float $latitude, float $longitude): string
    {
        $nearest = config('app.timezone', 'UTC');
        $shortest = INF;
        $cosLat = cos(deg2rad($latitude));

        foreach ($this->zoneLocations() as $zone => [$zoneLat, $zoneLng]) {
            $dLng = abs($longitude - $zoneLng);
            $dLng = min($dLng, 360 - $dLng) * $cosLat;
            $distance = ($latitude - $zoneLat) ** 2 + $dLng ** 2;

            if ($distance < $shortest) {
                $shortest = $distance;
                $nearest = $zone;
            }
        }

        return $nearest;
    }

    /**
     * @return array<string, string>
     */
    private function calculate(float $latitude, float $longitude, CarbonImmutable $day, string $timezone): array
    {
        $times = $this->calculator->calculate(
            latitude: $latitude,
            longitude: $longitude,
            year: $day->year,
            month: $day->month,
            day: $day->day,
            method: (string) config('prayer_times.calculation_method', 'egyptian'),
            asrMethod: (string) config('prayer_times.asr_method', 'standard'),
            highLatitudeRule: (string) config('prayer_times.high_latitude_rule', 'middle_of_night'),
            adjustments: (array) config('prayer_times.adjustments', []),
        );

        return array_map(
            fn (CarbonImmutable $utc) => $utc->setTimezone($timezone)->toIso8601String(),
            $times
        );
    }

    /**
     * @return array{key: string, name: string, time: string, datetime: string, remaining_seconds: int}
     */
    private function formatNextPrayer(string $key, CarbonInterface $at, CarbonInterface $now): array
    {
        return [
            'key' => $key,
            'name' => $this->name($key),
            'time' => $at->format('H:i'),
            'datetime' => $at->toIso8601String(),
            'remaining_seconds' => max(0, $at->getTimestamp() - $now->getTimestamp()),
        ];
    }

    /**
     * @return array<string, array{0: float, 1: float}>
     */
    private function zoneLocations(): array
    {
        if (self::$zoneLocations !== null) {
            return self::$zoneLocations;
        }

        $locations = [];

        foreach (DateTimeZone::listIdentifiers() as $identifier) {
            if ($identifier === 'UTC') {
                continue;
            }

            $location = (new DateTimeZone($identifier))->getLocation();

            if (! $location || ($location['country_code'] ?? '??') === '??') {
                continue;
            }

            $locations[$identifier] = [(float) $location['latitude'], (float) $location['longitude']];
        }

        return self::$zoneLocations = $locations;
    }
}
