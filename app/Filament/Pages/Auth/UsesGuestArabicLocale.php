<?php

namespace App\Filament\Pages\Auth;

use App\Support\Locales;

trait UsesGuestArabicLocale
{
    protected function applyGuestLocale(): void
    {
        app()->setLocale(Locales::default());

        if (session()->isStarted()) {
            session(['locale' => Locales::default()]);
        }
    }
}
