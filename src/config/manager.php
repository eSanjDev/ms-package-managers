<?php


return [
    'logo_path' => env("MANAGER_LOGO_PATH"),

    'success_redirect' => env('MANAGER_SUCCESS_REDIRECT', '/'),

    'routes' => [
        'prefix' => env('MANAGER_ROUTE_PREFIX', 'manager'),
        'middleware' => ['web'],
    ],

    'public_key_path' => env("MANAGER_PUBLIC_KEY_PATH", storage_path('oauth-public.key')),

    'cache' => [
        'is_enabled' => env('MANAGER_CACHE_ENABLED', true),
        'prefix' => env("MANAGER_CACHE_PREFIX", 'manager_'),
        'driver' => env('CACHE_DRIVER', 'file'),
        'ttl' => env('MANAGER_CACHE_TTL', 60 * 24 * 7),
    ],

    'rate_limit' => [
        'is_enabled' => env('MANAGER_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('MANAGER_RATE_LIMIT_MAX_ATTEMPTS', 1),
        'decay_seconds' => env('MANAGER_RATE_LIMIT_DECAY_SECONDS', 10 * 60), // 10 minutes
    ],
];
