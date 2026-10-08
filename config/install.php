<?php

return [

    /*
    |--------------------------------------------------------------------------
    | First Admin
    |--------------------------------------------------------------------------
    |
    | Used by AdminSeeder (php artisan app:install). When no password is set,
    | a random one is generated and printed once by the install command.
    |
    */

    'admin_name' => env('ADMIN_NAME', 'مدير سكينة'),

    'admin_email' => env('ADMIN_EMAIL', 'admin@sakina.test'),

    'admin_password' => env('ADMIN_PASSWORD'),

];
