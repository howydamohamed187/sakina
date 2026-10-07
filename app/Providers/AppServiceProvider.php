<?php

namespace App\Providers;

use App\Filament\Forms\TranslatableFields;
use App\Notifications\ResetPasswordNotification;
use App\Services\Mosques\Contracts\MosqueProvider;
use App\Services\Mosques\Providers\GooglePlacesProvider;
use App\Services\Mosques\Providers\OverpassProvider;
use App\Services\PrayerTimes\Contracts\PrayerTimesProvider;
use App\Services\PrayerTimes\Providers\AlAdhanPrayerTimesProvider;
use App\Services\PrayerTimes\Providers\LocalPrayerTimesProvider;
use App\Services\Quran\Contracts\QuranEditionProvider;
use App\Services\Quran\Providers\AlQuranCloudProvider;
use App\Support\Locales;
use App\Support\StoredSettings;
use Filament\Forms\Components\Section;
use Filament\Http\Controllers\Auth\LogoutController;
use Filament\Notifications\Auth\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            LogoutController::class,
            \App\Http\Controllers\Auth\LogoutController::class
        );

        $this->app->bind(MosqueProvider::class, fn (): MosqueProvider => match (config('services.places.provider')) {
            'google' => new GooglePlacesProvider(
                config('services.places.key'),
                (string) config('services.places.google_url'),
                (int) config('services.places.timeout', 10),
            ),
            default => new OverpassProvider(
                array_values((array) config('services.places.overpass_urls')),
                (int) config('services.places.overpass_timeout', 15),
                (int) config('services.places.overpass_budget', 25),
            ),
        });

        $this->app->bind(PrayerTimesProvider::class, fn ($app): PrayerTimesProvider => match (config('services.prayer_times.provider')) {
            'aladhan' => new AlAdhanPrayerTimesProvider(
                (string) config('services.prayer_times.url'),
                config('services.prayer_times.key'),
                (int) config('services.prayer_times.timeout', 10),
            ),
            default => $app->make(LocalPrayerTimesProvider::class),
        });

        $this->app->bind(QuranEditionProvider::class, fn (): QuranEditionProvider => new AlQuranCloudProvider(
            (string) config('services.quran.url'),
            config('services.quran.key'),
            (int) config('services.quran.timeout', 15),
        ));

        $this->app->bind(
            ResetPassword::class,
            fn ($app, array $params) => new ResetPasswordNotification($params['token'] ?? '')
        );
    }

    public function boot(): void
    {
        $locale = session('locale', Locales::default());

        if (Locales::isSupported((string) $locale)) {
            app()->setLocale($locale);
        }

        if (StoredSettings::debugMode()) {
            config(['app.debug' => true]);
        }

        Section::macro('translatable', function (?array $locales = null): Section {
            /** @var Section $this */
            return TranslatableFields::apply($this, $locales);
        });
    }
}
