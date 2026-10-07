<?php

namespace App\Services\PrayerTimes\Contracts;

use App\Services\Providers\ProviderException;
use Carbon\CarbonImmutable;

interface PrayerTimesProvider
{
    public function name(): string;

    /**
     * Times for the local calendar day of $day in $timezone, keyed by PrayerTimesService::TIMES.
     *
     * @return array<string, CarbonImmutable>
     *
     * @throws ProviderException
     */
    public function times(float $latitude, float $longitude, CarbonImmutable $day, string $timezone, string $method): array;
}
