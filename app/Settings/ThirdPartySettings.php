<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ThirdPartySettings extends Settings
{
    public ?string $google_map_key = null;

    public ?string $project_name = null;

    public ?string $firebase_file = null;

    public ?string $tranportal_id = null;

    public ?string $tranportal_password = null;

    public ?string $resource_key = null;

    public ?string $url = null;

    public bool $is_test = true;

    public static function group(): string
    {
        return 'third_party';
    }

    public static function encrypted(): array
    {
        return [
            'google_map_key',
            'tranportal_id',
            'tranportal_password',
            'resource_key',
        ];
    }
}
