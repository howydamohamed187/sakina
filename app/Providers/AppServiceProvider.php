<?php

namespace App\Providers;

use App\Filament\Forms\TranslatableFields;
use App\Notifications\ResetPasswordNotification;
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
