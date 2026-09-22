<?php

namespace App\Support;

use Illuminate\Http\Request;

class Locales
{
    public static function all(): array
    {
        return config('locales.supported', ['ar', 'en', 'ckb']);
    }

    public static function default(): string
    {
        return config('locales.default', 'ar');
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::all(), true);
    }

    public static function isRtl(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        return in_array($locale, config('locales.rtl', ['ar', 'ckb']), true);
    }

    public static function label(string $locale): string
    {
        return config("locales.labels.{$locale}", $locale);
    }

    public static function resolveFromRequest(Request $request): string
    {
        $candidates = [
            $request->header('X-Locale'),
            $request->query('locale'),
            $request->input('locale'),
        ];

        if ($request->user()) {
            $candidates[] = $request->user()->locale;
            $candidates[] = $request->hasSession() ? $request->session()->get('locale') : null;
        } elseif ($request->is('api/*') || $request->expectsJson()) {
            $candidates[] = $request->getPreferredLanguage(self::all());
        }

        $candidates[] = self::default();

        foreach ($candidates as $locale) {
            if (is_string($locale) && self::isSupported($locale)) {
                return $locale;
            }
        }

        return self::default();
    }

    public static function pick(mixed $value, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();

        if (! is_array($value)) {
            return $value;
        }

        return $value[$locale]
            ?? $value[self::default()]
            ?? $value['en']
            ?? reset($value);
    }
}
