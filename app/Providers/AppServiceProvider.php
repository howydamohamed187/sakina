<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\Http\Controllers\Auth\LogoutController::class,
            \App\Http\Controllers\Auth\LogoutController::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = session('locale', config('app.locale', 'ar'));

        if (in_array($locale, ['ar', 'en'], true)) {
            app()->setLocale($locale);
        }
    }
}
