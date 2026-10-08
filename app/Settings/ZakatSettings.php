<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ZakatSettings extends Settings
{
    public float $zakat_percentage = 2.5;

    public float $nisab_gold_grams = 85;

    public int $gold_karat = 21;

    public string $default_currency = 'IQD';

    public bool $is_active = true;

    public static function group(): string
    {
        return 'zakat';
    }

    /**
     * @return array{zakat_percentage: float, nisab_gold_grams: float, gold_karat: int, default_currency: string, is_active: bool}
     */
    public static function defaults(): array
    {
        return [
            'zakat_percentage' => 2.5,
            'nisab_gold_grams' => 85.0,
            'gold_karat' => 21,
            'default_currency' => 'IQD',
            'is_active' => true,
        ];
    }
}
