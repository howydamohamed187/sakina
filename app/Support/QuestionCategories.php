<?php

namespace App\Support;

class QuestionCategories
{
    public const RELIGIOUS = 'religious';

    public const GENERAL = 'general';

    public const SEERAH = 'seerah';

    public const QURAN = 'quran';

    public static function all(): array
    {
        return [self::RELIGIOUS, self::GENERAL, self::SEERAH, self::QURAN];
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

        $key = 'app.question_categories.'.$category;

        return trans()->has($key) ? __($key) : $category;
    }
}
