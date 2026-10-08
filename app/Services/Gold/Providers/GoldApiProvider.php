<?php

namespace App\Services\Gold\Providers;

use App\Services\Gold\Contracts\GoldPriceProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * https://gold-api.com — GET /price/XAU returns the 24K spot price per troy ounce in USD.
 */
class GoldApiProvider implements GoldPriceProvider
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $key,
        private readonly int $timeout,
    ) {}

    public function name(): string
    {
        return 'gold-api.com';
    }

    public function ouncePrice(): array
    {
        if ($this->url === '') {
            throw $this->error(ProviderException::NOT_CONFIGURED, 'Gold price URL is not configured.');
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->when($this->key, fn ($request) => $request->withHeaders(['x-api-key' => $this->key]))
                ->get(rtrim($this->url, '/').'/price/XAU');
        } catch (ConnectionException $e) {
            throw $this->error(ProviderException::TIMEOUT, $e->getMessage(), $e);
        }

        if ($response->failed()) {
            throw $this->error(ProviderException::FAILED, 'gold-api.com error '.$response->status().'.');
        }

        $price = $response->json('price');
        $currency = $response->json('currency');

        if (! is_numeric($price) || (float) $price <= 0 || ! is_string($currency) || strlen($currency) !== 3) {
            throw $this->error(ProviderException::INVALID_RESPONSE, 'gold-api.com returned an invalid price.');
        }

        $updatedAt = $response->json('updatedAt');

        return [
            'price' => (float) $price,
            'currency' => strtoupper($currency),
            'updated_at' => is_string($updatedAt) ? $updatedAt : null,
        ];
    }

    private function error(string $reason, string $message, ?Throwable $previous = null): ProviderException
    {
        return new ProviderException($reason, $message, $previous, ProviderException::SERVICE_GOLD_PRICE);
    }
}
