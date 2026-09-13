<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (! session()->has('locale')) {
            session(['locale' => 'ar']);
            app()->setLocale('ar');
        }
    }
}
