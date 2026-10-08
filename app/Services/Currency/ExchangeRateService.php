<?php

namespace App\Services\Currency;

use App\Services\Currency\Contracts\ExchangeRateProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Support\Facades\Cache;

class ExchangeRateService
{
    public function __construct(private readonly ExchangeRateProvider $provider) {}

    public function providerName(): string
    {
        return $this->provider->name();
    }

    /**
     * Units of $to for one unit of $from.
     *
     * @return array{rate: float, updated_at: string|null, is_stale: bool}
     *
     * @throws ProviderException
     */
    public function rate(string $from, string $to): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return ['rate' => 1.0, 'updated_at' => null, 'is_stale' => false];
        }

        $table = $this->table($from);
        $rate = $table['rates'][$to] ?? null;

        if (! $rate) {
            throw new ProviderException(
                ProviderException::INVALID_RESPONSE,
                "No {$from}->{$to} rate from ".$this->provider->name().'.',
                service: ProviderException::SERVICE_EXCHANGE_RATE,
            );
        }

        return ['rate' => $rate, 'updated_at' => $table['updated_at'], 'is_stale' => $table['is_stale']];
    }

    /**
     * @return array{rates: array<string, float>, updated_at: string|null, is_stale: bool}
     *
     * @throws ProviderException
     */
    private function table(string $base): array
    {
        $key = 'exchange_rates:'.now()->toDateString().':'.$this->provider->name().':'.$base;
        $staleKey = 'exchange_rates:last:'.$this->provider->name().':'.$base;

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached + ['is_stale' => false];
        }

        try {
            $table = $this->provider->rates($base);
        } catch (ProviderException $e) {
            $last = Cache::get($staleKey);

            if (is_array($last)) {
                return $last + ['is_stale' => true];
            }

            throw $e;
        }

        Cache::put($key, $table, (int) config('zakat.cache.rates_ttl', 21600));
        Cache::put($staleKey, $table, (int) config('zakat.cache.stale_ttl', 259200));

        return $table + ['is_stale' => false];
    }
}
