<?php

namespace App\Services\Mosques;

use App\Services\Mosques\Contracts\MosqueProvider;
use Illuminate\Support\Facades\Cache;

class MosqueService
{
    public const DEFAULT_RADIUS = 5000;

    public const DEFAULT_LIMIT = 10;

    public const MAX_RADIUS = 50000;

    private const GRID_MARGIN = 1000;

    public function __construct(private readonly MosqueProvider $provider) {}

    /**
     * @return list<array{mosque: Mosque, distance_km: float}> ordered by distance
     *
     * @throws MosqueProviderException
     */
    public function nearby(
        float $latitude,
        float $longitude,
        int $radius = self::DEFAULT_RADIUS,
        int $limit = self::DEFAULT_LIMIT,
        ?string $locale = null,
    ): array {
        $locale ??= app()->getLocale();

        // Nearby users share one fetch: snap to a ~1.1 km grid and widen the radius to cover the snap offset.
        $centerLat = round($latitude, 2);
        $centerLng = round($longitude, 2);
        $fetchRadius = min(self::MAX_RADIUS, $radius + self::GRID_MARGIN);

        $key = sprintf(
            'nearby_mosques:%s:%s:%d:%s:%s',
            number_format($centerLat, 2, '.', ''),
            number_format($centerLng, 2, '.', ''),
            $radius,
            $this->provider->name(),
            $locale,
        );

        $cached = Cache::get($key);

        if (! is_array($cached)) {
            try {
                $cached = array_map(
                    fn (Mosque $mosque): array => $mosque->toArray(),
                    $this->provider->nearby($centerLat, $centerLng, $fetchRadius, $locale)
                );
            } catch (MosqueProviderException $e) {
                $stale = Cache::get($key.':stale');

                if (! is_array($stale)) {
                    throw $e;
                }

                return $this->rank($stale, $latitude, $longitude, $radius, $limit);
            }

            Cache::put($key, $cached, (int) config('services.places.cache_ttl', 86400));
            Cache::put($key.':stale', $cached, (int) config('services.places.stale_ttl', 2592000));
        }

        return $this->rank($cached, $latitude, $longitude, $radius, $limit);
    }

    /**
     * @param  list<array<string, mixed>>  $cached
     * @return list<array{mosque: Mosque, distance_km: float}>
     */
    private function rank(array $cached, float $latitude, float $longitude, int $radius, int $limit): array
    {

        $results = [];

        foreach ($cached as $data) {
            $mosque = Mosque::fromArray($data);
            $meters = $this->distanceInMeters($latitude, $longitude, $mosque->latitude, $mosque->longitude);

            if ($meters <= $radius) {
                $results[$mosque->placeId] = ['mosque' => $mosque, 'distance_km' => round($meters / 1000, 2), 'meters' => $meters];
            }
        }

        usort($results, fn (array $a, array $b): int => $a['meters'] <=> $b['meters']);

        return array_map(
            fn (array $row): array => ['mosque' => $row['mosque'], 'distance_km' => $row['distance_km']],
            array_slice($results, 0, $limit)
        );
    }

    /**
     * @throws MosqueProviderException
     */
    public function photoUrl(string $reference): ?string
    {
        $url = Cache::remember(
            'mosque_photo:'.md5($this->provider->name().$reference),
            86400,
            fn (): string => (string) $this->provider->photoUrl($reference)
        );

        return $url !== '' ? $url : null;
    }

    public function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
