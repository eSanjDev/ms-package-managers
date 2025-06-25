# Manager Package

---

## Overview

Manager is a robust Laravel 12 package for OAuth admin authentication via an accounting microservice.  
It implements a two-step login flow:
1. OAuth 2.0 authentication with an external microservice
2. Static token confirmation for admin users

This ensures highly secure, segregated admin access for your application.  

---

## Features

- Laravel 12 & PHP 8.2+ support
- OAuth 2.0 with customizable accounting microservice
- Static token confirmation (after OAuth)
- Admin session & middleware protection (`CheckAccessTokenMiddleware`)
- UI with Livewire 3 components (RTL/LTR)
- Rate limiting for brute-force protection
- Configurable, publishable views and assets
- Persian and English language support  

---

## Requirements

1. **PHP >= 8.2**
2. **Laravel >= 12.x**
3. **Livewire >= 3.6**
4. **Access to an OAuth-powered accounting microservice**

---

## Installation

### 1. Install via Composer

```sh
composer require esanj/ms-package-managers
```

### 2. Run the package installer
```sh
php artisan manager:install
```

This will:

- Publish config files to config/
- Publish static assets to public/assets/vendor/manager
- Run required migrations to create the oauth_managers table

### 3. Configure Environment

Set the following variables in your .env file:

`CLIENT_ID=your-app-client-id
`

`SECRET_ID=your-app-secret-id
`

`
ACCOUNTING_BASE_URL=https://your-accounting-microservice.com
`

ACCOUNTING_BASE_URL is the full base URI for your accounting microservice.

**CLIENT_ID and SECRET_ID are the credentials for your application obtained from the accounting microservice.**

### 4. Add oauth_public.key file

Copy the public key used to verify JWT tokens to the following path:

`
storage/oauth-public.key
`

(You can obtain this from your accounting service admin.)

---

## Usage

- ### OAuth Flow

They're redirected to the OAuth login page of your accounting microservice

Upon successful login, callback saves the access/refresh token in session

User enters their static manager token (second step)

On success, session is authorized as admin

- ### Protecting Routes

To ensure only logged-in managers can access certain pages, apply the middleware:

```php
use Esanj\Manager\Middleware\CheckAccessTokenMiddleware;

Route::middleware([CheckAccessTokenMiddleware::class])->group(function () {
    // your protected admin routes
});
```
---

## Creating Manager Accounts
You can create new admin (manager) records with this command:

Example:
```sh
php artisan manager:create
```
If the manager ID is already registered, you'll get an error.
Token is hashed for storage.

---

## Customizing

The Manager package is fully customizable. You can publish and override every part using artisan commands:

### Publish Languages

To publish the language files (for translation editing):

```sh
php artisan vendor:publish --provider="Esanj\Manager\Providers\ManagerServiceProvider" --tag=manager-lang
```

Edit the files in: `lang/**/manager.php`

### Publish Migrations

To publish only the migration files (if you want to modify the table structure):

```sh
php artisan vendor:publish --provider="Esanj\Manager\Providers\ManagerServiceProvider" --tag=manager-migrations
```

Edit the files in: `database/migrations/`

### Publish Assets

To publish static assets (CSS, JS, images, fonts):

```sh
php artisan vendor:publish --provider="Esanj\Manager\Providers\ManagerServiceProvider" --tag=manager-assets
```

After publishing you can edit: `public/assets/vendor/manager/`

### Publish Views

To customize Blade views:

```sh
php artisan vendor:publish --provider="Esanj\Manager\Providers\ManagerServiceProvider" --tag=manager-views
```

Edit views in: `resources/views/vendor/manager/`

### Publish Config
You can also publish the config file if you want to update default settings, such as route prefix, cache, redirects, etc:

```sh
php artisan vendor:publish --provider="Esanj\Manager\Providers\ManagerServiceProvider" --tag=manager-config
```

Edit: `config/manager.php`

---

## Credits
Developed and maintained by the Esanj Team.
Pull requests and issues are welcome.
