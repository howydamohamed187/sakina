<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DeveloperSettings extends Settings
{
    public bool $debug_mode = false;

    public bool $otp_code_is_random = true;

    public static function group(): string
    {
        return 'developer';
    }
}
