<?php

namespace App\Services\Currency\Providers;

use App\Services\Currency\Contracts\ExchangeRateProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * ExchangeRate-API (https://www.exchangerate-api.com). Open access: GET {url}/latest/{base};
 * with a key: GET {url}/{key}/latest/{base}.
 */
class ExchangeRateApiProvider implements ExchangeRateProvider
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $key,
        private readonly int $timeout,
    ) {}

    public function name(): string
    {
        return 'exchangerate-api.com';
    }

    public function rates(string $base): array
    {
        if ($this->url === '') {
            throw $this->error(ProviderException::NOT_CONFIGURED, 'Exchange rates URL is not configured.');
        }

        $path = (filled($this->key) ? '/'.$this->key : '').'/latest/'.strtoupper($base);

        try {
            $response = Http::timeout($this->timeout)->acceptJson()->get(rtrim($this->url, '/').$path);
        } catch (ConnectionException $e) {
            throw $this->error(ProviderException::TIMEOUT, $e->getMessage(), $e);
        }

        if ($response->failed() || $response->json('result') !== 'success') {
            throw $this->error(ProviderException::FAILED, 'ExchangeRate-API error '.$response->status().'.');
        }

        $rates = $response->json('rates') ?? $response->json('conversion_rates');

        if (! is_array($rates) || $rates === []) {
            throw $this->error(ProviderException::INVALID_RESPONSE, 'ExchangeRate-API returned no rates.');
        }

        $clean = [];

        foreach ($rates as $code => $rate) {
            if (is_string($code) && is_numeric($rate) && (float) $rate > 0) {
                $clean[strtoupper($code)] = (float) $rate;
            }
        }

        $updatedAt = $response->json('time_last_update_unix');

        return [
            'rates' => $clean,
            'updated_at' => is_numeric($updatedAt) ? now()->setTimestamp((int) $updatedAt)->utc()->toIso8601String() : null,
        ];
    }

    private function error(string $reason, string $message, ?Throwable $previous = null): ProviderException
    {
        return new ProviderException($reason, $message, $previous, ProviderException::SERVICE_EXCHANGE_RATE);
    }
}
