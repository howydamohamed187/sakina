<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('api.otp_mail_subject'))
            ->line(__('api.otp_mail_line', ['code' => $this->code]))
            ->line(__('api.otp_mail_expiry', ['minutes' => config('customers.otp_ttl_minutes')]));
    }
}
