<?php

namespace App\Notifications;

use App\Support\Locales;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminMessageNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, string>  $title
     * @param  array<string, string>  $body
     */
    public function __construct(
        public array $title,
        public array $body,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'key' => 'admin_message',
            'title' => Locales::pick($this->title, $locale),
            'body' => Locales::pick($this->body, $locale),
            'title_i18n' => $this->title,
            'body_i18n' => $this->body,
        ];
    }
}
