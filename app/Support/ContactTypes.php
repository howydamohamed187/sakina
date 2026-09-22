<?php

namespace App\Support;

class ContactTypes
{
    public const LINK = 'link';

    public const ACCOUNT = 'account_number';

    public const PHONE = 'phone';

    public const HOTLINE = 'hotline';

    public static function all(): array
    {
        return [self::LINK, self::ACCOUNT, self::PHONE, self::HOTLINE];
    }

    public static function options(): array
    {
        return collect(self::all())
            ->mapWithKeys(fn (string $type): array => [$type => self::label($type)])
            ->all();
    }

    public static function isPhone(?string $type): bool
    {
        return in_array($type, [self::PHONE, self::HOTLINE], true);
    }

    public static function label(string $type): string
    {
        $key = 'app.contact_types.'.$type;

        return trans()->has($key) ? __($key) : $type;
    }

    public static function valueLabel(?string $type): string
    {
        return match ($type) {
            self::LINK => __('app.fields.contact_link'),
            self::ACCOUNT => __('app.fields.account_number'),
            self::PHONE => __('app.fields.phone'),
            self::HOTLINE => __('app.fields.hotline'),
            default => __('app.fields.contact_value'),
        };
    }
}
