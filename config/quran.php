<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quran Source
    |--------------------------------------------------------------------------
    |
    | Verbatim Tanzil Quran Text (Simple, v1.1), CC BY 3.0. The importer refuses
    | to load the file if its checksum differs, so the text cannot drift.
    |
    */

    'source_path' => database_path('data/quran/quran-simple.sql'),

    'source_sha256' => '315290a5f3e161bd5e34e211b7659dcee3fe5a2ae86d90a4ee22f78425217816',

    'source' => [
        'name' => 'Tanzil Project',
        'url' => 'https://tanzil.net',
        'license' => 'CC BY 3.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Locales
    |--------------------------------------------------------------------------
    |
    | Used when no translation/tafsir exists for the request locale.
    |
    */

    'translation_fallback_locale' => 'en',

    'tafsir_fallback_locale' => 'ar',

];
