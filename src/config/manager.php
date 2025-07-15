<?php


return [
    'logo_path' => env("MANAGER_LOGO_PATH"),

    'success_redirect' => env('MANAGER_SUCCESS_REDIRECT', '/'),

    'access_denied_redirect' => env('MANAGER_ACCESS_DENIED_REDIRECT', '/'),

    'token_length' => 32,

    'routes' => [
        'auth_prefix' => env('MANAGER_AUTH_ROUTE_PREFIX', 'manager'),
        'panel_prefix' => env('MANAGER_PANEL_ROUTE_PREFIX', 'managers'),
        'api_prefix' => env('MANAGER_API_ROUTE_PREFIX', 'api/managers'),
    ],

    'public_key_path' => env("MANAGER_PUBLIC_KEY_PATH", storage_path('oauth-public.key')),

    'cache' => [
        'is_enabled' => env('MANAGER_CACHE_ENABLED', true),
        'prefix' => env("MANAGER_CACHE_PREFIX", 'manager_6'),
        'driver' => env('CACHE_DRIVER', 'file'),
        'ttl' => env('MANAGER_CACHE_TTL', 60 * 24 * 7),
    ],

    'rate_limit' => [
        'is_enabled' => env('MANAGER_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('MANAGER_RATE_LIMIT_MAX_ATTEMPTS', 10),
        'decay_seconds' => env('MANAGER_RATE_LIMIT_DECAY_SECONDS', 10 * 60), // 10 minutes
    ],

    "permissions" => [
        'managers.edit' => [
            'display_name' => 'Edit Managers',
            'description' => 'Allows editing of manager details',
        ],
        'managers.list' => [
            'display_name' => 'List Managers',
            'description' => 'Allows viewing the list of managers',
        ],
        'managers.update' => [
            'display_name' => 'Update Managers',
            'description' => 'Allows updating manager information',
        ],
        'managers.delete' => [
            'display_name' => 'Delete Managers',
            'description' => 'Allows deletion of managers',
        ],
    ],
];
