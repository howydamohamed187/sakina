<?php

namespace App\Support;

use App\Settings\AppearanceSettings;
use App\Settings\DeveloperSettings;
use App\Settings\GeneralSettings;
use App\Settings\ThirdPartySettings;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StoredSettings
{
    public static function general(): ?GeneralSettings
    {
        return self::safe(GeneralSettings::class);
    }

    public static function appearance(): ?AppearanceSettings
    {
        return self::safe(AppearanceSettings::class);
    }

    public static function developer(): ?DeveloperSettings
    {
        return self::safe(DeveloperSettings::class);
    }

    public static function thirdParty(): ?ThirdPartySettings
    {
        return self::safe(ThirdPartySettings::class);
    }

    public static function appName(?string $locale = null): string
    {
        $names = self::general()?->app_name;
        $locale ??= app()->getLocale();

        if (is_array($names)) {
            foreach ([$locale, 'ar', 'en'] as $key) {
                if (filled($names[$key] ?? null)) {
                    return (string) $names[$key];
                }
            }
        }

        if (is_string($names) && $names !== '') {
            return $names;
        }

        return __('app.name');
    }

    public static function logoUrl(): ?string
    {
        return self::publicUrl(self::general()?->app_logo);
    }

    public static function faviconUrl(): ?string
    {
        return self::publicUrl(self::general()?->fav_icon);
    }

    public static function fontFamily(): string
    {
        $family = self::appearance()?->font_family ?: Fonts::DEFAULT_FAMILY;

        return Fonts::isSupported($family) ? $family : Fonts::DEFAULT_FAMILY;
    }

    public static function fontSize(): string
    {
        $size = self::appearance()?->font_size ?: Fonts::DEFAULT_SIZE;

        return Fonts::isSupportedSize($size) ? $size : Fonts::DEFAULT_SIZE;
    }

    public static function fontSizePx(): string
    {
        return Fonts::sizePx(self::fontSize());
    }

    public static function googleMapKey(): ?string
    {
        $key = self::thirdParty()?->google_map_key;

        return filled($key) ? $key : null;
    }

    public static function otpIsRandom(): bool
    {
        return self::developer()?->otp_code_is_random ?? true;
    }

    public static function debugMode(): bool
    {
        return self::developer()?->debug_mode ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public static function publicGeneral(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $settings = self::general();
        $defaults = GeneralSettings::defaults();

        return [
            'site_name' => self::appName($locale),
            'app_name' => self::appName($locale),
            'app_logo' => self::logoUrl() ?? '',
            'favicon' => self::faviconUrl() ?? '',
            'app_email' => $settings?->app_email,
            'app_phone' => $settings?->app_phone,
            'app_mobile' => $settings?->app_mobile,
            'app_whatsapp' => $settings?->app_whatsapp,
            'app_address' => Locales::pick($settings?->app_address, $locale),
            'app_latitude' => $settings?->app_latitude ?? $defaults['app_latitude'],
            'app_longitude' => $settings?->app_longitude ?? $defaults['app_longitude'],
            'applications_links' => $settings?->applications_links ?: $defaults['applications_links'],
            'social_links' => self::socialLinks($settings?->social_links),
        ];
    }

    /**
     * @param  array<int|string, mixed>|null  $links
     * @return array<int, array{key: string|int, icon: string|null, url: string}>
     */
    private static function socialLinks(?array $links): array
    {
        return collect($links ?: [])
            ->map(function (mixed $item, int|string $key): ?array {
                if (is_array($item)) {
                    $url = (string) ($item['url'] ?? $item['link'] ?? '');
                    if ($url === '') {
                        return null;
                    }

                    return [
                        'icon' => $item['icon'] ?? null,
                        'url' => $url,
                    ];
                }

                if (! is_string($item) || $item === '') {
                    return null;
                }

                return [
                    'icon' => null,
                    'url' => $item,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T|null
     */
    private static function safe(string $class): ?object
    {
        try {
            if (! Schema::hasTable(config('settings.repositories.database.table') ?? 'settings')) {
                return null;
            }

            $settings = app($class);
            $settings->toArray();

            return $settings;
        } catch (Throwable) {
            return null;
        }
    }
}
