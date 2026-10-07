<?php

namespace App\Services\Mosques\Providers;

use App\Services\Mosques\Contracts\MosqueProvider;
use App\Services\Mosques\Mosque;
use App\Services\Mosques\MosqueProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * OpenStreetMap via the Overpass API. Data © OpenStreetMap contributors (ODbL).
 */
class OverpassProvider implements MosqueProvider
{
    /**
     * @param  list<string>  $urls  public instances are often overloaded, so later URLs act as fallbacks
     */
    public function __construct(
        private readonly array $urls,
        private readonly int $timeout,
        private readonly int $budget = 25,
    ) {}

    public function name(): string
    {
        return 'overpass';
    }

    public function nearby(float $latitude, float $longitude, int $radius, string $locale): array
    {
        $around = sprintf('(around:%d,%F,%F)', $radius, $latitude, $longitude);
        $filter = '["amenity"="place_of_worship"]["religion"="muslim"]';
        $query = sprintf(
            '[out:json][timeout:%d];(node%s%s;way%s%s;relation%s%s;);out center tags;',
            $this->timeout, $filter, $around, $filter, $around, $filter, $around
        );

        $error = new MosqueProviderException(MosqueProviderException::NOT_CONFIGURED, 'No Overpass URL configured.');

        // The budget keeps the whole fallback chain under PHP's max_execution_time.
        $deadline = microtime(true) + $this->budget;

        foreach ($this->urls as $url) {
            $remaining = (int) floor($deadline - microtime(true));

            if ($remaining < 3) {
                break;
            }

            try {
                return $this->map($this->fetch($url, $query, min($this->timeout, $remaining)), $locale);
            } catch (MosqueProviderException $e) {
                $error = $e;
            }
        }

        throw $error;
    }

    /**
     * @return array<int, mixed>
     */
    private function fetch(string $url, string $query, int $timeout): array
    {
        try {
            $response = Http::timeout($timeout)
                ->asForm()
                ->withHeaders(['User-Agent' => config('app.name').' nearby-mosques'])
                ->post($url, ['data' => $query]);
        } catch (ConnectionException $e) {
            throw new MosqueProviderException(MosqueProviderException::TIMEOUT, $e->getMessage(), $e);
        }

        if ($response->status() === 504 || $response->status() === 429) {
            throw new MosqueProviderException(MosqueProviderException::TIMEOUT, 'Overpass is busy ('.$response->status().').');
        }

        if ($response->failed()) {
            throw new MosqueProviderException(MosqueProviderException::FAILED, 'Overpass error '.$response->status().'.');
        }

        $elements = $response->json('elements');

        if (! is_array($elements)) {
            throw new MosqueProviderException(MosqueProviderException::INVALID_RESPONSE, 'Overpass returned an unexpected payload.');
        }

        return $elements;
    }

    /**
     * @param  array<int, mixed>  $elements
     * @return list<Mosque>
     */
    private function map(array $elements, string $locale): array
    {
        $mosques = [];

        foreach ($elements as $element) {
            $lat = $element['lat'] ?? data_get($element, 'center.lat');
            $lng = $element['lon'] ?? data_get($element, 'center.lon');

            if (! isset($element['type'], $element['id']) || ! is_numeric($lat) || ! is_numeric($lng)) {
                continue;
            }

            $tags = $element['tags'] ?? [];

            $mosques[] = new Mosque(
                placeId: "osm:{$element['type']}/{$element['id']}",
                name: (string) ($tags["name:{$locale}"] ?? $tags['name'] ?? $tags['name:ar'] ?? $tags['name:en'] ?? __('api.mosques.unnamed')),
                address: $this->address($tags),
                latitude: (float) $lat,
                longitude: (float) $lng,
            );
        }

        return $mosques;
    }

    public function photoUrl(string $reference): ?string
    {
        return null;
    }

    /**
     * @param  array<string, string>  $tags
     */
    private function address(array $tags): ?string
    {
        if (filled($tags['addr:full'] ?? null)) {
            return $tags['addr:full'];
        }

        $street = trim(($tags['addr:housenumber'] ?? '').' '.($tags['addr:street'] ?? ''));
        $parts = array_filter([
            $street,
            $tags['addr:suburb'] ?? $tags['addr:district'] ?? null,
            $tags['addr:city'] ?? null,
        ], 'filled');

        return $parts ? implode('، ', $parts) : null;
    }
}
