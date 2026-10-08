<?php

return [

    /*
    | Currencies the calculator accepts, with the number of decimals amounts are
    | rounded to. Every code must exist in the exchange-rate provider's feed.
    */
    'currencies' => [
        'IQD' => 0,
        'USD' => 2,
        'EUR' => 2,
        'GBP' => 2,
        'EGP' => 2,
        'SAR' => 2,
        'AED' => 2,
        'KWD' => 3,
        'QAR' => 2,
        'BHD' => 3,
        'OMR' => 3,
        'JOD' => 3,
        'LBP' => 0,
        'SYP' => 0,
        'ILS' => 2,
        'YER' => 0,
        'TRY' => 2,
    ],

    /*
    | Currency used when only ?country= is sent (ISO 3166-1 alpha-2).
    */
    'country_currencies' => [
        'IQ' => 'IQD',
        'EG' => 'EGP',
        'SA' => 'SAR',
        'AE' => 'AED',
        'KW' => 'KWD',
        'QA' => 'QAR',
        'BH' => 'BHD',
        'OM' => 'OMR',
        'JO' => 'JOD',
        'LB' => 'LBP',
        'SY' => 'SYP',
        'PS' => 'ILS',
        'YE' => 'YER',
        'TR' => 'TRY',
        'US' => 'USD',
        'GB' => 'GBP',
    ],

    'karats' => [24, 22, 21, 18],

    'max_amount' => 1_000_000_000_000_000,

    'cache' => [
        'gold_ttl' => (int) env('ZAKAT_GOLD_CACHE_TTL', 10800),
        'rates_ttl' => (int) env('ZAKAT_RATES_CACHE_TTL', 21600),
        'nisab_ttl' => (int) env('ZAKAT_NISAB_CACHE_TTL', 3600),
        'stale_ttl' => (int) env('ZAKAT_STALE_TTL', 259200),
    ],
];
