<?php

namespace App\Http\Controllers\Api;

use App\Services\PrayerTimesService;
use App\Services\Providers\ProviderException;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PrayerTimesController extends ApiController
{
    public function __construct(private readonly PrayerTimesService $prayerTimes) {}

    public function index(Request $request): JsonResponse
    {
        $data = Validator::make([
            'latitude' => $request->query('latitude', $request->header('X-Latitude')),
            'longitude' => $request->query('longitude', $request->header('X-Longitude')),
            'timezone' => $request->query('timezone', $request->header('X-Timezone')),
            'date' => $request->query('date'),
            'method' => $request->query('method'),
        ], [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'timezone' => ['nullable', 'string', 'timezone:all'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'method' => ['nullable', 'string', Rule::in(PrayerTimesService::methods())],
        ])->validate();

        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];
        $timezone = $data['timezone'] ?? $this->prayerTimes->resolveTimezone($latitude, $longitude);
        $method = $data['method'] ?? $this->prayerTimes->defaultMethod();
        $date = isset($data['date'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $data['date'], $timezone)
            : CarbonImmutable::now($timezone);

        try {
            $times = $this->prayerTimes->getPrayerTimes($latitude, $longitude, $date, $timezone, $method);
        } catch (ProviderException $e) {
            return $this->error($e->userMessage(), [], $e->status());
        }

        return $this->success([
            'date' => $date->toDateString(),
            'timezone' => $timezone,
            'method' => $method,
            'provider' => $this->prayerTimes->providerName(),
            ...collect(PrayerTimesService::TIMES)->mapWithKeys(fn (string $key): array => [$key => $times[$key]->format('H:i')])->all(),
            'times' => $this->prayerTimes->toList($times),
        ], __('api.prayer_times_ready'));
    }
}
