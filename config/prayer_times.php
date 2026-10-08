<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Calculation Method
    |--------------------------------------------------------------------------
    |
    | Supported: mwl, isna, egyptian, makkah, karachi, gulf, kuwait, qatar,
    | singapore, france, turkey.
    |
    */

    'calculation_method' => env('PRAYER_CALCULATION_METHOD', 'egyptian'),

    /*
    |--------------------------------------------------------------------------
    | Asr Method
    |--------------------------------------------------------------------------
    |
    | Supported: standard (Shafi'i, Maliki, Hanbali), hanafi.
    |
    */

    'asr_method' => env('PRAYER_ASR_METHOD', 'standard'),

    /*
    |--------------------------------------------------------------------------
    | High Latitude Rule
    |--------------------------------------------------------------------------
    |
    | Supported: middle_of_night, seventh_of_night, twilight_angle, none.
    |
    */

    'high_latitude_rule' => env('PRAYER_HIGH_LATITUDE_RULE', 'middle_of_night'),

    /*
    |--------------------------------------------------------------------------
    | Adjustments
    |--------------------------------------------------------------------------
    |
    | Minutes added to (or subtracted from) each calculated time.
    |
    */

    'adjustments' => [
        'fajr' => (int) env('PRAYER_ADJUST_FAJR', 0),
        'sunrise' => (int) env('PRAYER_ADJUST_SUNRISE', 0),
        'dhuhr' => (int) env('PRAYER_ADJUST_DHUHR', 0),
        'asr' => (int) env('PRAYER_ADJUST_ASR', 0),
        'maghrib' => (int) env('PRAYER_ADJUST_MAGHRIB', 0),
        'isha' => (int) env('PRAYER_ADJUST_ISHA', 0),
    ],

    'cache_ttl' => (int) env('PRAYER_CACHE_TTL', 60 * 60 * 48),

    /*
    |--------------------------------------------------------------------------
    | Prayer Notifications
    |--------------------------------------------------------------------------
    |
    | Defaults apply to prayers the customer has not configured yet. A prayer
    | is pushed when its time falls within the last "window_minutes" minutes.
    |
    */

    'notifications' => [
        'defaults' => [
            'fajr' => ['enabled' => true, 'sound_enabled' => true, 'vibration_enabled' => true],
            'sunrise' => ['enabled' => false, 'sound_enabled' => false, 'vibration_enabled' => false],
            'dhuhr' => ['enabled' => true, 'sound_enabled' => true, 'vibration_enabled' => true],
            'asr' => ['enabled' => true, 'sound_enabled' => true, 'vibration_enabled' => true],
            'maghrib' => ['enabled' => true, 'sound_enabled' => true, 'vibration_enabled' => true],
            'isha' => ['enabled' => true, 'sound_enabled' => true, 'vibration_enabled' => true],
        ],
        'window_minutes' => (int) env('PRAYER_NOTIFICATION_WINDOW', 5),
    ],

];
