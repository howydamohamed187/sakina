<?php

namespace App\Support;

class Fonts
{
    public const DEFAULT_FAMILY = 'Alexandria';

    public const DEFAULT_SIZE = 'base';

    /**
     * @return array<string, string>
     */
    public static function families(): array
    {
        return [
            'Cairo' => 'Cairo (ar)',
            'Alexandria' => 'Alexandria (ar)',
            'Tajawal' => 'Tajawal (ar)',
            'Almarai' => 'Almarai (ar)',
            'Rubik' => 'Rubik (ar)',
            'Noto Kufi Arabic' => 'Noto Kufi Arabic (ar)',
            'IBM Plex Sans Arabic' => 'IBM Plex Sans Arabic (ar)',
            'Readex Pro' => 'Readex Pro (ar)',
            'Noto Naskh Arabic' => 'Noto Naskh Arabic (ar)',
            'Vazirmatn' => 'Vazirmatn (ar)',
            'Amiri' => 'Amiri (ar)',
            'Changa' => 'Changa (ar)',
            'El Messiri' => 'El Messiri (ar)',
            'Harmattan' => 'Harmattan (ar)',
            'Reem Kufi' => 'Reem Kufi (ar)',
            'Roboto' => 'Roboto (en)',
            'Open Sans' => 'Open Sans (en)',
            'Lato' => 'Lato (en)',
            'Montserrat' => 'Montserrat (en)',
            'Poppins' => 'Poppins (en)',
            'Nunito' => 'Nunito (en)',
            'Inter' => 'Inter (en)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sizes(): array
    {
        return [
            'sm' => '14px',
            'base' => '16px',
            'lg' => '18px',
            'xl' => '20px',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sizeOptions(): array
    {
        return [
            'sm' => __('settings.font_size_sm'),
            'base' => __('settings.font_size_base'),
            'lg' => __('settings.font_size_lg'),
            'xl' => __('settings.font_size_xl'),
        ];
    }

    public static function isSupported(string $family): bool
    {
        return array_key_exists($family, self::families());
    }

    public static function isSupportedSize(string $size): bool
    {
        return array_key_exists($size, self::sizes());
    }

    public static function sizePx(string $size): string
    {
        return self::sizes()[$size] ?? self::sizes()[self::DEFAULT_SIZE];
    }

    public static function googleCssUrl(string $family): string
    {
        $family = str_replace(' ', '+', $family);

        return "https://fonts.googleapis.com/css2?family={$family}:wght@400;500;600;700&display=swap";
    }
}
