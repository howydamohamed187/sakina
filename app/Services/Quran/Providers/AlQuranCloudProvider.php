<?php

namespace App\Services\Quran\Providers;

use App\Services\Providers\ProviderException;
use App\Services\Quran\Contracts\QuranEditionProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * @see https://alquran.cloud/api
 */
class AlQuranCloudProvider implements QuranEditionProvider
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $key,
        private readonly int $timeout,
    ) {}

    public function name(): string
    {
        return 'alquran.cloud';
    }

    public function surah(int $surah, string $edition): array
    {
        if ($this->url === '') {
            throw new ProviderException(ProviderException::NOT_CONFIGURED, 'Quran API URL is not configured.');
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->when($this->key, fn ($request) => $request->withToken($this->key))
                ->get(rtrim($this->url, '/')."/surah/{$surah}/{$edition}");
        } catch (ConnectionException $e) {
            throw new ProviderException(ProviderException::TIMEOUT, $e->getMessage(), $e);
        }

        if ($response->failed()) {
            throw new ProviderException(ProviderException::FAILED, "alquran.cloud error {$response->status()} for {$edition}.");
        }

        $ayahs = $response->json('data.ayahs');

        if (! is_array($ayahs) || $ayahs === []) {
            throw new ProviderException(ProviderException::INVALID_RESPONSE, "alquran.cloud returned no ayahs for {$edition}.");
        }

        $texts = [];

        foreach ($ayahs as $ayah) {
            $number = $ayah['numberInSurah'] ?? null;
            $text = $ayah['text'] ?? null;

            if (! is_int($number) || ! is_string($text) || trim($text) === '') {
                throw new ProviderException(ProviderException::INVALID_RESPONSE, "alquran.cloud returned a malformed ayah for {$edition}.");
            }

            $texts[$number] = trim($text);
        }

        return $texts;
    }
}
