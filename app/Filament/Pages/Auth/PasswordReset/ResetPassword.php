<?php

namespace App\Filament\Pages\Auth\PasswordReset;

use App\Filament\Pages\Auth\UsesGuestArabicLocale;
use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    use UsesGuestArabicLocale;

    public function mount(?string $email = null, ?string $token = null): void
    {
        $this->applyGuestLocale();

        parent::mount($email, $token);
    }
}
