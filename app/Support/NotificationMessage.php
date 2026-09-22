<?php

namespace App\Support;

use Illuminate\Notifications\DatabaseNotification;

class NotificationMessage
{
    public static function title(DatabaseNotification $notification): string
    {
        return self::text($notification, 'title');
    }

    public static function body(DatabaseNotification $notification): string
    {
        return self::text($notification, 'body');
    }

    protected static function text(DatabaseNotification $notification, string $field): string
    {
        $data = is_array($notification->data) ? $notification->data : [];

        if (isset($data[$field.'_i18n'])) {
            return (string) (Locales::pick($data[$field.'_i18n']) ?: '');
        }

        if (isset($data[$field])) {
            return (string) (Locales::pick($data[$field]) ?: '');
        }

        $key = $data['key'] ?? null;

        return $key ? (string) __("notifications.{$key}.{$field}") : '';
    }
}
