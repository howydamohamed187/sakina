<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RuqyahSettings extends Settings
{
    public string $title = 'الرقية الشرعية';

    public string $description = '';

    public static function group(): string
    {
        return 'ruqyah';
    }

    /**
     * @return array{title: string, description: string}
     */
    public static function defaults(): array
    {
        return [
            'title' => 'الرقية الشرعية',
            'description' => 'يمكن أن تمارس الرقية على نفسك مباشرة أو تحظى بمساعدة شخص قريب منك يقرأ عليك أو تقرأ عليه',
        ];
    }
}
