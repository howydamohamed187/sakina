<?php

namespace App\Services\PrayerTimes\Providers;

use App\Services\PrayerTimes\Contracts\PrayerTimesProvider;
use App\Services\PrayerTimesService;
use App\Services\Providers\ProviderException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * @see https://aladhan.com/prayer-times-api
 */
class AlAdhanPrayerTimesProvider implements PrayerTimesProvider
{
    /** Our method keys mapped to AlAdhan method ids. */
    public const METHODS = [
        'karachi' => 1,
        'isna' => 2,
        'mwl' => 3,
        'makkah' => 4,
        'egyptian' => 5,
        'gulf' => 8,
        'kuwait' => 9,
        'qatar' => 10,
        'singapore' => 11,
        'france' => 12,
        'turkey' => 13,
    ];

    private const FIELDS = [
        'fajr' => 'Fajr',
        'sunrise' => 'Sunrise',
        'dhuhr' => 'Dhuhr',
        'asr' => 'Asr',
        'maghrib' => 'Maghrib',
        'isha' => 'Isha',
    ];

    public function __construct(
        private readonly string $url,
        private readonly ?string $key,
        private readonly int $timeout,
    ) {}

    public function name(): string
    {
        return 'aladhan';
    }

    public function times(float $latitude, float $longitude, CarbonImmutable $day, string $timezone, string $method): array
    {
        if ($this->url === '') {
            throw new ProviderException(ProviderException::NOT_CONFIGURED, 'AlAdhan URL is not configured.');
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->when($this->key, fn ($request) => $request->withHeaders(['X-Api-Key' => $this->key]))
                ->get(rtrim($this->url, '/').'/timings/'.$day->format('d-m-Y'), [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'method' => self::METHODS[$method] ?? self::METHODS['egyptian'],
                    'school' => config('prayer_times.asr_method') === 'hanafi' ? 1 : 0,
                    'timezonestring' => $timezone,
                ]);
        } catch (ConnectionException $e) {
            throw new ProviderException(ProviderException::TIMEOUT, $e->getMessage(), $e);
        }

        if ($response->failed()) {
            throw new ProviderException(ProviderException::FAILED, 'AlAdhan error '.$response->status().'.');
        }

        $timings = $response->json('data.timings');

        if (! is_array($timings)) {
            throw new ProviderException(ProviderException::INVALID_RESPONSE, 'AlAdhan returned no timings.');
        }

        $times = [];

        foreach (PrayerTimesService::TIMES as $key) {
            $value = $timings[self::FIELDS[$key]] ?? null;

            if (! is_string($value) || ! preg_match('/^(\d{1,2}):(\d{2})/', $value, $match)) {
                throw new ProviderException(ProviderException::INVALID_RESPONSE, "AlAdhan returned an invalid {$key} time.");
            }

            $times[$key] = $day->setTimezone($timezone)->setTime((int) $match[1], (int) $match[2]);
        }

        return $times;
    }
}
