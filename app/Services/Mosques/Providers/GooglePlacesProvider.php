<?php

namespace App\Services\Mosques\Providers;

use App\Services\Mosques\Contracts\MosqueProvider;
use App\Services\Mosques\Mosque;
use App\Services\Mosques\MosqueProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Google Places API (New): https://developers.google.com/maps/documentation/places/web-service/nearby-search
 */
class GooglePlacesProvider implements MosqueProvider
{
    private const MAX_RESULTS = 20;

    private const LANGUAGES = ['ar' => 'ar', 'en' => 'en', 'ckb' => 'ar'];

    public function __construct(
        private readonly ?string $key,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    public function name(): string
    {
        return 'google';
    }

    public function nearby(float $latitude, float $longitude, int $radius, string $locale): array
    {
        $response = $this->send(fn () => Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'X-Goog-Api-Key' => $this->key(),
                'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.shortFormattedAddress,places.location,places.photos',
            ])
            ->post('places:searchNearby', [
                'includedTypes' => ['mosque'],
                'maxResultCount' => self::MAX_RESULTS,
                'rankPreference' => 'DISTANCE',
                'languageCode' => self::LANGUAGES[$locale] ?? 'en',
                'locationRestriction' => [
                    'circle' => [
                        'center' => ['latitude' => $latitude, 'longitude' => $longitude],
                        'radius' => (float) $radius,
                    ],
                ],
            ]));

        $places = $response->json('places', []);

        if (! is_array($places)) {
            throw new MosqueProviderException(MosqueProviderException::INVALID_RESPONSE, 'Google Places returned an unexpected payload.');
        }

        $mosques = [];

        foreach ($places as $place) {
            $lat = data_get($place, 'location.latitude');
            $lng = data_get($place, 'location.longitude');

            if (! isset($place['id']) || ! is_numeric($lat) || ! is_numeric($lng)) {
                continue;
            }

            $mosques[] = new Mosque(
                placeId: (string) $place['id'],
                name: (string) (data_get($place, 'displayName.text') ?: __('api.mosques.unnamed')),
                address: data_get($place, 'formattedAddress') ?: data_get($place, 'shortFormattedAddress'),
                latitude: (float) $lat,
                longitude: (float) $lng,
                photoReference: data_get($place, 'photos.0.name'),
            );
        }

        return $mosques;
    }

    public function photoUrl(string $reference): ?string
    {
        if (! preg_match('#^places/[^/]+/photos/[^/]+$#', $reference)) {
            return null;
        }

        $response = $this->send(fn () => Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->get("{$reference}/media", [
                'maxWidthPx' => 800,
                'skipHttpRedirect' => 'true',
                'key' => $this->key(),
            ]));

        $uri = $response->json('photoUri');

        return is_string($uri) && str_starts_with($uri, 'https://') ? $uri : null;
    }

    private function key(): string
    {
        if (blank($this->key)) {
            throw new MosqueProviderException(MosqueProviderException::NOT_CONFIGURED, 'PLACES_API_KEY is not set.');
        }

        return $this->key;
    }

    /**
     * @param  callable(): Response  $request
     */
    private function send(callable $request): Response
    {
        try {
            $response = $request();
        } catch (ConnectionException $e) {
            throw new MosqueProviderException(MosqueProviderException::TIMEOUT, $e->getMessage(), $e);
        }

        if ($response->failed()) {
            throw new MosqueProviderException(
                MosqueProviderException::FAILED,
                'Google Places error '.$response->status().': '.$response->json('error.message', '')
            );
        }

        if (! is_array($response->json())) {
            throw new MosqueProviderException(MosqueProviderException::INVALID_RESPONSE, 'Google Places returned non-JSON.');
        }

        return $response;
    }
}
