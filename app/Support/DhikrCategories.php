<?php

namespace App\Support;

class DhikrCategories
{
    public const MORNING = 'morning';

    public const EVENING = 'evening';

    public const SLEEP = 'sleep';

    public const PRAYER = 'prayer';

    public static function all(): array
    {
        return [self::MORNING, self::EVENING, self::SLEEP, self::PRAYER];
    }

    public static function options(): array
    {
        return collect(self::all())
            ->mapWithKeys(fn (string $category): array => [$category => self::label($category)])
            ->all();
    }

    public static function label(?string $category): string
    {
        if (! $category) {
            return __('app.fields.uncategorized');
        }

        $key = 'app.dhikr_categories.'.$category;

        return trans()->has($key) ? __($key) : $category;
    }
}
