<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Logo Path
    |--------------------------------------------------------------------------
    |
    | Path to the logo image displayed in the manager panel.
    |
    */
    'logo_path' => env('MANAGER_LOGO_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Redirect URLs
    |--------------------------------------------------------------------------
    |
    | URLs for redirecting after login success or access denial.
    |
    */
    'success_redirect' => env('MANAGER_SUCCESS_REDIRECT', '/'),
    'access_denied_redirect' => env('MANAGER_ACCESS_DENIED_REDIRECT', '/'),

    /*
    |--------------------------------------------------------------------------
    | Access Token Expiration
    |--------------------------------------------------------------------------
    |
    | Expiration time for access tokens in minutes.
    | Default: 1440 minutes (24 hours)
    |
    */
    'access_token_expires_in' => (int) env('MANAGER_ACCESS_TOKEN_TTL', 1440),

    /*
    |--------------------------------------------------------------------------
    | API Only Mode
    |--------------------------------------------------------------------------
    |
    | If true, the package will only provide API routes without any web panel.
    |
    */
    'just_api' => env('MANAGER_JUST_API', false),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Prefixes for different route types.
    |
    */
    'routes' => [
        'auth_prefix' => env('MANAGER_AUTH_ROUTE_PREFIX', 'admin'),
        'panel_prefix' => env('MANAGER_PANEL_ROUTE_PREFIX', 'admin'),
        'api_prefix' => env('MANAGER_API_ROUTE_PREFIX', 'api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Middleware stacks for API and web routes.
    |
    */
    'middlewares' => [
        'api' => [
            'api',
            'manager.auth:api',
        ],
        'web' => [
            'web',
            'manager.auth:web',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Public Key
    |--------------------------------------------------------------------------
    |
    | Path to the public key file for OAuth manager authentication.
    |
    */
    'public_key_path' => env('MANAGER_PUBLIC_KEY_PATH', storage_path('oauth-public.key')),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for caching manager data.
    |
    */
    'cache' => [
        'is_enabled' => env('MANAGER_CACHE_ENABLED', true),
        'prefix' => env('MANAGER_CACHE_PREFIX', 'manager_'),
        'driver' => env('CACHE_STORE', 'file'),
        'ttl' => (int) env('MANAGER_CACHE_TTL', 60 * 24 * 7), // 7 days in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for manager authentication and actions.
    |
    */
    'rate_limit' => [
        'is_enabled' => env('MANAGER_RATE_LIMIT_ENABLED', true),
        'max_attempts' => (int) env('MANAGER_RATE_LIMIT_MAX_ATTEMPTS', 10),
        'decay_seconds' => (int) env('MANAGER_RATE_LIMIT_DECAY_SECONDS', 600), // 10 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Available permissions for manager roles.
    |
    */
    'permissions' => [
        'managers.list' => [
            'display_name' => 'List Managers',
            'description' => 'Allows viewing the list of managers',
        ],
        'managers.create' => [
            'display_name' => 'Create Manager',
            'description' => 'Allows creating new managers',
        ],
        'managers.edit' => [
            'display_name' => 'Edit Managers',
            'description' => 'Allows editing manager details',
        ],
        'managers.delete' => [
            'display_name' => 'Delete Managers',
            'description' => 'Allows deletion of managers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Provider Mapping
    |--------------------------------------------------------------------------
    |
    | Maps controller actions to required permissions.
    |
    */
    'access_provider' => [
        'list' => 'managers.list',
        'store' => 'managers.create',
        'update' => 'managers.edit',
        'delete' => 'managers.delete',
        'restore' => 'managers.delete',
        'activity' => 'managers.list',
        'meta' => 'managers.list',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extra Fields
    |--------------------------------------------------------------------------
    |
    | Blade components to be included in the manager edit panel.
    | Example: 'content.manager.limit'
    |
    */
    'extra_field' => [
        // 'content.manager.limit',
    ],
];