<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LocalizedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $key,
        public array $replace = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $locales = config('locales.supported', ['ar', 'en', 'ckb']);
        $title = [];
        $body = [];

        foreach ($locales as $locale) {
            $title[$locale] = __("notifications.{$this->key}.title", $this->replace, $locale);
            $body[$locale] = __("notifications.{$this->key}.body", $this->replace, $locale);
        }

        $userLocale = $notifiable->locale ?? app()->getLocale();

        return [
            'key' => $this->key,
            'title' => $title[$userLocale] ?? $title['ar'],
            'body' => $body[$userLocale] ?? $body['ar'],
            'title_i18n' => $title,
            'body_i18n' => $body,
        ];
    }
}
