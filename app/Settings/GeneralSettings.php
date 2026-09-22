<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $app_logo = null;

    public ?string $fav_icon = null;

    public array $app_name = [
        'ar' => 'سكينة',
        'en' => 'Sakina',
    ];

    public ?string $app_email = null;

    public ?string $app_phone = null;

    public ?string $app_mobile = null;

    public ?string $app_whatsapp = null;

    public ?string $app_address = null;

    public ?string $app_latitude = null;

    public ?string $app_longitude = null;

    public array $refund_rules = [];

    public array $applications_links = [];

    public array $app_details = [];

    public array $working_days = [];

    public array $social_links = [];

    public static function group(): string
    {
        return 'general';
    }

    public static function defaults(): array
    {
        return [
            'app_logo' => null,
            'fav_icon' => null,
            'app_name' => [
                'ar' => 'سكينة',
                'en' => 'Sakina',
            ],
            'app_email' => null,
            'app_phone' => null,
            'app_mobile' => null,
            'app_whatsapp' => null,
            'app_address' => null,
            'app_latitude' => '24.7136',
            'app_longitude' => '46.6753',
            'refund_rules' => [
                'duration' => 30,
                'refunded_percentage' => 100,
            ],
            'applications_links' => [
                'google_play_link' => null,
                'apple_store_link' => null,
            ],
            'app_details' => [
                'android' => [
                    'version' => '1.0.0',
                    'is_visitor' => false,
                    'is_maintenance' => false,
                ],
                'ios' => [
                    'version' => '1.0.0',
                    'is_visitor' => false,
                    'is_maintenance' => false,
                ],
            ],
            'working_days' => [],
            'social_links' => [],
        ];
    }
}
