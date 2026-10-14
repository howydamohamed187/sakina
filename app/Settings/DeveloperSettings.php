<?php

namespace App\Settings;

use App\Settings\Casts\NativeTypeCast;
use Spatie\LaravelSettings\Settings;

class DeveloperSettings extends Settings
{
    public bool $debug_mode = false;

    public bool $otp_code_is_random = true;

    public static function group(): string
    {
        return 'developer';
    }

    public static function casts(): array
    {
        return [
            'debug_mode' => new NativeTypeCast('bool'),
            'otp_code_is_random' => new NativeTypeCast('bool'),
        ];
    }
}
