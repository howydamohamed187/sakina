<?php

namespace App\Support;

class DuaCategories
{
    public const DISTRESS = 'distress';

    public const GENERAL = 'general';

    public const TRAVEL = 'travel';

    public const GUIDANCE = 'guidance';

    public static function all(): array
    {
        return [self::DISTRESS, self::GENERAL, self::TRAVEL, self::GUIDANCE];
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

        $key = 'app.dua_categories.'.$category;

        return trans()->has($key) ? __($key) : $category;
    }
}
