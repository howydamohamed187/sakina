<?php

namespace App\Notifications;

use Filament\Notifications\Auth\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? config('locales.default', 'ar');
        $minutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject(__('notifications.reset_password.subject', [], $locale))
            ->line(__('notifications.reset_password.line', [], $locale))
            ->action(__('notifications.reset_password.action', [], $locale), $this->url)
            ->line(__('notifications.reset_password.expire', ['minutes' => $minutes], $locale));
    }
}
