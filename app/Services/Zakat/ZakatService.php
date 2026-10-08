<?php

namespace App\Services\Zakat;

use App\Services\Currency\ExchangeRateService;
use App\Services\Gold\GoldPriceService;
use App\Services\Providers\ProviderException;
use App\Settings\ZakatSettings;
use App\Support\StoredSettings;
use Illuminate\Support\Facades\Cache;

/**
 * Nisab = nisab_gold_grams × price of one gram of gold at gold_karat, converted to the
 * requested currency. Zakat = amount × zakat_percentage / 100 when amount >= nisab.
 * Amounts are computed with bcmath so large values keep full precision.
 */
class ZakatService
{
    private const SCALE = 10;

    public function __construct(
        private readonly GoldPriceService $gold,
        private readonly ExchangeRateService $rates,
    ) {}

    /**
     * @return array{zakat_percentage: float, nisab_gold_grams: float, gold_karat: int, default_currency: string, is_active: bool}
     */
    public function settings(): array
    {
        $stored = StoredSettings::zakat();
        $defaults = ZakatSettings::defaults();

        if (! $stored) {
            return $defaults;
        }

        $currency = strtoupper($stored->default_currency);

        return [
            'zakat_percentage' => (float) $stored->zakat_percentage,
            'nisab_gold_grams' => (float) $stored->nisab_gold_grams,
            'gold_karat' => (int) $stored->gold_karat,
            'default_currency' => self::isSupported($currency) ? $currency : $defaults['default_currency'],
            'is_active' => (bool) $stored->is_active,
        ];
    }

    public function isActive(): bool
    {
        return $this->settings()['is_active'];
    }

    /**
     * @return array<int, string>
     */
    public static function currencies(): array
    {
        return array_keys((array) config('zakat.currencies'));
    }

    public static function isSupported(string $currency): bool
    {
        return array_key_exists(strtoupper($currency), (array) config('zakat.currencies'));
    }

    public static function decimals(string $currency): int
    {
        return (int) (config('zakat.currencies.'.strtoupper($currency)) ?? 2);
    }

    public static function currencyForCountry(?string $country): ?string
    {
        return $country ? config('zakat.country_currencies.'.strtoupper($country)) : null;
    }

    public function resolveCurrency(?string $currency, ?string $country = null): string
    {
        return strtoupper($currency ?: (self::currencyForCountry($country) ?? $this->settings()['default_currency']));
    }

    /**
     * @return array{
     *     nisab_value: int|float, currency: string, gold_grams: float, gold_karat: int,
     *     gold_price_per_gram: float, zakat_percentage: float, exchange_rate: float,
     *     gold_price_source: string, exchange_rate_source: string|null,
     *     gold_price_updated_at: string|null, exchange_rate_updated_at: string|null,
     *     is_stale: bool, calculated_at: string
     * }
     *
     * @throws ProviderException
     */
    public function nisab(string $currency): array
    {
        $currency = strtoupper($currency);
        $settings = $this->settings();
        $key = sprintf(
            'zakat_nisab:%s:%s:%s:%s',
            now()->toDateString(),
            $currency,
            $settings['gold_karat'],
            $settings['nisab_gold_grams'],
        );

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached + ['zakat_percentage' => $settings['zakat_percentage']];
        }

        $gram = $this->gold->pricePerGram($settings['gold_karat']);
        $rate = $this->rates->rate($gram['currency'], $currency);
        $gramPrice = $gram['price_per_gram'] * $rate['rate'];
        $decimals = self::decimals($currency);

        $nisab = [
            'nisab_value' => $this->number(
                $this->round(bcmul($this->decimal($gramPrice), $this->decimal($settings['nisab_gold_grams']), self::SCALE), $decimals),
                $decimals,
            ),
            'currency' => $currency,
            'gold_grams' => $settings['nisab_gold_grams'],
            'gold_karat' => $settings['gold_karat'],
            'gold_price_per_gram' => round($gramPrice, 2),
            'exchange_rate' => $rate['rate'],
            'gold_price_source' => $gram['source'],
            'exchange_rate_source' => $gram['currency'] === $currency ? null : $this->rates->providerName(),
            'gold_price_updated_at' => $gram['updated_at'],
            'exchange_rate_updated_at' => $rate['updated_at'],
            'is_stale' => $gram['is_stale'] || $rate['is_stale'],
            'calculated_at' => now()->utc()->toIso8601ZuluString(),
        ];

        $ttl = $nisab['is_stale'] ? 300 : (int) config('zakat.cache.nisab_ttl', 3600);
        Cache::put($key, $nisab, $ttl);

        return $nisab + ['zakat_percentage' => $settings['zakat_percentage']];
    }

    /**
     * Nothing about the amount is stored or logged.
     *
     * @return array{
     *     entered_amount: int|float, currency: string,
     *     nisab: array{value: int|float, gold_grams: float, gold_karat: int, gold_price_per_gram: float},
     *     is_zakat_due: bool, zakat_percentage: float, zakat_amount: int|float,
     *     is_stale: bool, calculated_at: string
     * }
     *
     * @throws ProviderException
     */
    public function calculate(string|int|float $amount, string $currency): array
    {
        $currency = strtoupper($currency);
        $decimals = self::decimals($currency);
        $nisab = $this->nisab($currency);
        $amount = $this->decimal($amount);
        $due = bccomp($amount, $this->decimal($nisab['nisab_value']), self::SCALE) >= 0;

        $zakat = $due
            ? $this->round(bcdiv(bcmul($amount, $this->decimal($nisab['zakat_percentage']), self::SCALE), '100', self::SCALE), $decimals)
            : '0';

        return [
            'entered_amount' => $this->number($amount, null),
            'currency' => $currency,
            'nisab' => [
                'value' => $nisab['nisab_value'],
                'gold_grams' => $nisab['gold_grams'],
                'gold_karat' => $nisab['gold_karat'],
                'gold_price_per_gram' => $nisab['gold_price_per_gram'],
            ],
            'is_zakat_due' => $due,
            'zakat_percentage' => $nisab['zakat_percentage'],
            'zakat_amount' => $this->number($zakat, $decimals),
            'is_stale' => $nisab['is_stale'],
            'calculated_at' => now()->utc()->toIso8601ZuluString(),
        ];
    }

    private function decimal(string|int|float $value): string
    {
        if (is_float($value)) {
            return rtrim(rtrim(sprintf('%.'.self::SCALE.'F', $value), '0'), '.') ?: '0';
        }

        return bcadd((string) $value, '0', self::SCALE);
    }

    private function round(string $value, int $decimals): string
    {
        $half = '0.'.str_repeat('0', $decimals).'5';

        return bcadd($value, str_starts_with($value, '-') ? '-'.$half : $half, $decimals);
    }

    private function number(string $value, ?int $decimals): int|float
    {
        $value = $decimals === null ? $value : bcadd($value, '0', $decimals);
        $whole = bcadd($value, '0', 0);

        return bccomp($value, $whole, self::SCALE) === 0 ? (int) $whole : (float) $value;
    }
}
