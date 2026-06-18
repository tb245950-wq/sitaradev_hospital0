<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi pembatasan permintaan untuk keamanan aplikasi SITARA.
    |
    */

    'login' => [
        'max_attempts' => env('RATELIMIT_LOGIN_MAX', 5),
        'decay_minutes' => env('RATELIMIT_LOGIN_DECAY', 1),
    ],

    'booking' => [
        'max_attempts' => env('RATELIMIT_BOOKING_MAX', 10),
        'decay_minutes' => env('RATELIMIT_BOOKING_DECAY', 1),
    ],
];
