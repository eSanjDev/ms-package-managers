<?php


return [
    // Logo path for the manager panel
    'logo_path' => env("MANAGER_LOGO_PATH"),

    // Redirect path after successful login
    'success_redirect' => env('MANAGER_SUCCESS_REDIRECT', '/'),

    // Redirect path when access is denied
    'access_denied_redirect' => env('MANAGER_ACCESS_DENIED_REDIRECT', '/'),

    // Expiration time for access tokens in minutes
    'access_token_expires_in' => 1440, // Minutes (24 hours)

    // If true, the package will only provide API routes without any web panel
    'just_api' => env('MANAGER_JUST_API', false),

    // Manager route prefixes
    'routes' => [
        'auth_prefix' => env('MANAGER_AUTH_ROUTE_PREFIX', 'admin'),
        'panel_prefix' => env('MANAGER_PANEL_ROUTE_PREFIX', 'admin'),
        'api_prefix' => env('MANAGER_API_ROUTE_PREFIX', 'api'),
    ],

    // Middlewares for different route types
    'middlewares' => [
        'api' => [
            'api',
            'manager.auth:api'
        ],

        'web' => [
            'web',
            'manager.auth:web',
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

    "access_provider" => [
        'list' => 'managers.list',
        'store' => 'managers.create',
        'update' => 'managers.edit',
        'delete' => 'managers.delete',
        'restore' => 'managers.delete',
        'activity' => 'managers.list',
        'meta' => 'managers.list',
    ],

    // Blade components that can be used in the manager edit panel
    'extra_field' => [
        // Example: "content.manager.limit",
    ],
];
