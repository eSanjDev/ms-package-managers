<?php


return [
    'logo_path' => env("MANAGER_LOGO_PATH"),

    'success_redirect' => env('MANAGER_SUCCESS_REDIRECT', '/'),

    'access_denied_redirect' => env('MANAGER_ACCESS_DENIED_REDIRECT', '/'),

    'token_length' => 32,

    'just_api' => env('MANAGER_JUST_API', false),

    // Manager route prefixes
    'routes' => [
        'auth_prefix' => env('MANAGER_AUTH_ROUTE_PREFIX', 'admin'),
        'panel_prefix' => env('MANAGER_PANEL_ROUTE_PREFIX', 'admin'),
        'api_prefix' => env('MANAGER_API_ROUTE_PREFIX', 'api'),
    ],

    'middlewares' => [
        'api' => [
            "api"
        ],

        'web' => [
            'web'
        ]
    ],

    // Public key path for OAuth manager authentication
    'public_key_path' => env("MANAGER_PUBLIC_KEY_PATH", storage_path('oauth-public.key')),

    // Private key path for OAuth manager authentication
    'cache' => [
        'is_enabled' => env('MANAGER_CACHE_ENABLED', true),
        'prefix' => env("MANAGER_CACHE_PREFIX", 'manager_'),
        'driver' => env('CACHE_STORE', 'file'),
        'ttl' => env('MANAGER_CACHE_TTL', 60 * 24 * 7),
    ],

    // Rate limiting configuration for manager actions
    'rate_limit' => [
        'is_enabled' => env('MANAGER_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('MANAGER_RATE_LIMIT_MAX_ATTEMPTS', 10),
        'decay_seconds' => env('MANAGER_RATE_LIMIT_DECAY_SECONDS', 10 * 60), // 10 minutes
    ],

    // Permissions for managers
    "permissions" => [
        'managers.edit' => [
            'display_name' => 'Edit Managers',
            'description' => 'Allows editing of manager details',
        ],
        'managers.list' => [
            'display_name' => 'List Managers',
            'description' => 'Allows viewing the list of managers',
        ],
        'managers.create' => [
            'display_name' => 'Create Manager',
            'description' => 'Allows create new manager',
        ],
        'managers.delete' => [
            'display_name' => 'Delete Managers',
            'description' => 'Allows deletion of managers',
        ],
    ],

    // Blade components that can be used in the manager edit panel
    'extra_field' => [
        // Example: "content.manager.limit",
    ],
];
