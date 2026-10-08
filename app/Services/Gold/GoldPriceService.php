<?php

namespace App\Services\Gold;

use App\Services\Gold\Contracts\GoldPriceProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Support\Facades\Cache;

class GoldPriceService
{
    public const GRAMS_PER_TROY_OUNCE = 31.1034768;

    public const PURE_KARAT = 24;

    public function __construct(private readonly GoldPriceProvider $provider) {}

    /**
     * Price of one gram at the given karat, in the provider's currency.
     *
     * @return array{price_per_gram: float, currency: string, karat: int, source: string, updated_at: string|null, is_stale: bool}
     *
     * @throws ProviderException
     */
    public function pricePerGram(int $karat): array
    {
        $spot = $this->spot();

        return [
            'price_per_gram' => $spot['price'] / self::GRAMS_PER_TROY_OUNCE * $karat / self::PURE_KARAT,
            'currency' => $spot['currency'],
            'karat' => $karat,
            'source' => $this->provider->name(),
            'updated_at' => $spot['updated_at'],
            'is_stale' => $spot['is_stale'],
        ];
    }

    /**
     * A provider outage falls back to the last good price (up to zakat.cache.stale_ttl old), flagged as stale.
     *
     * @return array{price: float, currency: string, updated_at: string|null, is_stale: bool}
     *
     * @throws ProviderException
     */
    public function spot(): array
    {
        $key = 'gold_price:'.now()->toDateString().':'.$this->provider->name().':'.self::PURE_KARAT;
        $staleKey = 'gold_price:last:'.$this->provider->name();

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached + ['is_stale' => false];
        }

        try {
            $spot = $this->provider->ouncePrice();
        } catch (ProviderException $e) {
            $last = Cache::get($staleKey);

            if (is_array($last)) {
                return $last + ['is_stale' => true];
            }

            throw $e;
        }

        Cache::put($key, $spot, (int) config('zakat.cache.gold_ttl', 10800));
        Cache::put($staleKey, $spot, (int) config('zakat.cache.stale_ttl', 259200));

        return $spot + ['is_stale' => false];
    }
}
