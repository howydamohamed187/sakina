<?php

namespace App\Support;

class PhoneNumber
{
    public const DEFAULT_COUNTRY = 'EG';

    /**
     * @return array<string, array{dial: string, digits: int, prefixes: array<int, string>}>
     */
    public static function countries(): array
    {
        return [
            'EG' => ['dial' => '20', 'digits' => 10, 'prefixes' => ['10', '11', '12', '15']],
            'SA' => ['dial' => '966', 'digits' => 9, 'prefixes' => ['5']],
            'AE' => ['dial' => '971', 'digits' => 9, 'prefixes' => ['5']],
            'KW' => ['dial' => '965', 'digits' => 8, 'prefixes' => ['5', '6', '9']],
            'QA' => ['dial' => '974', 'digits' => 8, 'prefixes' => ['3', '5', '6', '7']],
            'BH' => ['dial' => '973', 'digits' => 8, 'prefixes' => ['3']],
            'OM' => ['dial' => '968', 'digits' => 8, 'prefixes' => ['7', '9']],
            'IQ' => ['dial' => '964', 'digits' => 10, 'prefixes' => ['7']],
            'JO' => ['dial' => '962', 'digits' => 9, 'prefixes' => ['7']],
            'LB' => ['dial' => '961', 'digits' => 8, 'prefixes' => ['3', '7']],
            'SY' => ['dial' => '963', 'digits' => 9, 'prefixes' => ['9']],
            'PS' => ['dial' => '970', 'digits' => 9, 'prefixes' => ['5']],
            'YE' => ['dial' => '967', 'digits' => 9, 'prefixes' => ['7']],
            'TR' => ['dial' => '90', 'digits' => 10, 'prefixes' => ['5']],
            'US' => ['dial' => '1', 'digits' => 10, 'prefixes' => []],
            'GB' => ['dial' => '44', 'digits' => 10, 'prefixes' => ['7']],
        ];
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::countries() as $iso => $country) {
            $options[$iso] = __('app.phone.countries.'.$iso).' (+'.$country['dial'].')';
        }

        return $options;
    }

    public static function compactOptions(): array
    {
        $options = [];

        foreach (self::countries() as $iso => $country) {
            $options[$iso] = self::flag($iso).' +'.$country['dial'];
        }

        return $options;
    }

    public static function flag(string $iso): string
    {
        $iso = strtoupper($iso);
        $flag = '';

        foreach (str_split($iso) as $letter) {
            $flag .= mb_chr(ord($letter) + 127397);
        }

        return $flag;
    }

    public static function normalizeDigits(string $value): string
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        $value = str_replace($eastern, $western, $value);
        $value = str_replace($persian, $western, $value);

        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public static function nationalFromStored(?string $stored, string $country = self::DEFAULT_COUNTRY): string
    {
        if (! $stored) {
            return '';
        }

        $digits = self::normalizeDigits($stored);
        $dial = self::countries()[$country]['dial'] ?? '';

        if ($dial !== '' && str_starts_with($digits, $dial)) {
            $digits = substr($digits, strlen($dial));
        }

        return ltrim($digits, '0');
    }

    public static function countryFromStored(?string $stored): string
    {
        if (! $stored) {
            return self::DEFAULT_COUNTRY;
        }

        $digits = self::normalizeDigits($stored);

        $sorted = collect(self::countries())
            ->sortByDesc(fn (array $country) => strlen($country['dial']));

        foreach ($sorted as $iso => $country) {
            if (str_starts_with($digits, $country['dial'])) {
                return $iso;
            }
        }

        return self::DEFAULT_COUNTRY;
    }

    public static function toE164(?string $country, ?string $national): ?string
    {
        $country = $country ?: self::DEFAULT_COUNTRY;
        $national = self::normalizeDigits((string) $national);

        if ($national === '') {
            return null;
        }

        $dial = self::countries()[$country]['dial'] ?? null;

        if (! $dial) {
            return null;
        }

        if (str_starts_with($national, $dial)) {
            $national = substr($national, strlen($dial));
        }

        $national = ltrim($national, '0');

        return '+'.$dial.$national;
    }

    public static function isValid(?string $country, ?string $national): bool
    {
        $country = $country ?: self::DEFAULT_COUNTRY;
        $meta = self::countries()[$country] ?? null;

        if (! $meta) {
            return false;
        }

        $e164 = self::toE164($country, $national);

        if (! $e164) {
            return false;
        }

        $national = self::nationalFromStored($e164, $country);

        if (strlen($national) !== $meta['digits']) {
            return false;
        }

        if ($meta['prefixes'] === []) {
            return true;
        }

        foreach ($meta['prefixes'] as $prefix) {
            if (str_starts_with($national, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public static function format(?string $stored): ?string
    {
        if (! $stored) {
            return null;
        }

        $country = self::countryFromStored($stored);
        $dial = self::countries()[$country]['dial'] ?? '';
        $national = self::nationalFromStored($stored, $country);

        if ($national === '') {
            return $stored;
        }

        return '+'.$dial.' '.$national;
    }
}
