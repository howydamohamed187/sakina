<?php

namespace App\Filament\Pages\Auth\PasswordReset;

use App\Filament\Pages\Auth\UsesGuestArabicLocale;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Support\Facades\Password;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    use UsesGuestArabicLocale;

    public function mount(): void
    {
        $this->applyGuestLocale();

        parent::mount();
    }

    protected function getFailureNotification(string $status): ?Notification
    {
        if ($status === Password::INVALID_USER) {
            return $this->getSentNotification(Password::RESET_LINK_SENT);
        }

        return parent::getFailureNotification($status);
    }
}
