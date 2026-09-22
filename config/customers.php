<?php

return [
    'default_password' => env('CUSTOMER_DEFAULT_PASSWORD', 'password'),
    'otp_ttl_minutes' => 10,
    'otp_length' => 4,
    'otp_static' => env('CUSTOMER_OTP_STATIC', '1234'),
];
