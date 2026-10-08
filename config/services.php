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

    /*
    | Prayer times: "local" (built-in astronomical calculator, no network) or
    | "aladhan" (https://aladhan.com/prayer-times-api, no key required).
    */
    'prayer_times' => [
        'provider' => env('PRAYER_TIMES_PROVIDER', 'local'),
        'url' => env('PRAYER_TIMES_API_URL', 'https://api.aladhan.com/v1'),
        'key' => env('PRAYER_TIMES_API_KEY'),
        'timeout' => (int) env('PRAYER_TIMES_TIMEOUT', 10),
    ],

    /*
    | Quran translations and tafsir (the Arabic text itself is the local Tanzil
    | source). "alquran_cloud": https://alquran.cloud/api, no key required.
    */
    'quran' => [
        'provider' => env('QURAN_PROVIDER', 'alquran_cloud'),
        'url' => env('QURAN_API_URL', 'https://api.alquran.cloud/v1'),
        'key' => env('QURAN_API_KEY'),
        'timeout' => (int) env('QURAN_TIMEOUT', 15),
    ],

    /*
    | Gold spot price (24K, per troy ounce, USD). "gold_api": https://gold-api.com,
    | no key required (an optional key raises the rate limit).
    */
    'gold_price' => [
        'provider' => env('GOLD_PRICE_PROVIDER', 'gold_api'),
        'url' => env('GOLD_PRICE_API_URL', 'https://api.gold-api.com'),
        'key' => env('GOLD_PRICE_API_KEY'),
        'timeout' => (int) env('GOLD_PRICE_TIMEOUT', 10),
    ],

    /*
    | Exchange rates. "exchangerate_api": ExchangeRate-API. Without a key the
    | open-access endpoint (https://open.er-api.com/v6, daily rates) is used; with
    | a key set EXCHANGE_RATES_API_URL=https://v6.exchangerate-api.com/v6.
    */
    'exchange_rates' => [
        'provider' => env('EXCHANGE_RATES_PROVIDER', 'exchangerate_api'),
        'url' => env('EXCHANGE_RATES_API_URL', 'https://open.er-api.com/v6'),
        'key' => env('EXCHANGE_RATES_API_KEY'),
        'timeout' => (int) env('EXCHANGE_RATES_TIMEOUT', 10),
    ],

    /*
    | Smart Assistant AI provider. "openai" speaks the OpenAI Chat Completions API,
    | so any compatible provider works by changing AI_BASE_URL / AI_MODEL
    | (OpenAI, OpenRouter, Groq, DeepSeek, Gemini's OpenAI-compatible endpoint...).
    | The key is server-side only and never returned to the app.
    */
    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'url' => env('AI_BASE_URL', 'https://api.openai.com/v1'),
        'key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_TIMEOUT', 30),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 1000),
        'temperature' => (float) env('AI_TEMPERATURE', 0.3),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
