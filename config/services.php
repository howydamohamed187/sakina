<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    /*
    | Nearby mosques provider: "google" (Places API New, needs PLACES_API_KEY)
    | or "overpass" (OpenStreetMap, no key).
    */
    'places' => [
        'provider' => env('PLACES_PROVIDER', 'overpass'),
        'key' => env('PLACES_API_KEY'),
        'timeout' => (int) env('PLACES_TIMEOUT', 10),
        'cache_ttl' => (int) env('PLACES_CACHE_TTL', 86400),
        'google_url' => env('PLACES_GOOGLE_URL', 'https://places.googleapis.com/v1'),
        'overpass_urls' => array_filter(explode(',', (string) env(
            'PLACES_OVERPASS_URLS',
            'https://overpass-api.de/api/interpreter,https://maps.mail.ru/osm/tools/overpass/api/interpreter'
        ))),
        'overpass_timeout' => (int) env('PLACES_OVERPASS_TIMEOUT', 15),
        'overpass_budget' => (int) env('PLACES_OVERPASS_BUDGET', 25),
        'stale_ttl' => (int) env('PLACES_STALE_TTL', 2592000),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
