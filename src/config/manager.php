<?php


return [
    'client_id' => env('CLIENT_ID'),
    'secret_id' => env('SECRET_ID'),

    'state_ttl' => env('STATE_TTL', 300), // 5 minutes

    'cache' => [
        'is_enabled' => env('CACHE_ENABLED', true),
        'prefix' => 'oauth_manager_',
        'driver' => env('CACHE_DRIVER', 'file'),
        'ttl' => env('CACHE_TTL', 60 * 24 * 7), // 7 days
    ],

    'prefix' => 'oauth',

    'redirect_to' => '/',

    'base_url' => env('ACCOUNTING_BASE_URL', 'http://127.0.0.1:8000'),

    'logo_path' => '/assets/vendor/manager/img/logo.png',
];
