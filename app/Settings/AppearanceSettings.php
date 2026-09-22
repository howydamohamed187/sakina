<?php

namespace App\Settings;

use App\Support\Fonts;
use Spatie\LaravelSettings\Settings;

class AppearanceSettings extends Settings
{
    public string $font_family = Fonts::DEFAULT_FAMILY;

    public string $font_size = Fonts::DEFAULT_SIZE;

    public static function group(): string
    {
        return 'appearance';
    }
}
