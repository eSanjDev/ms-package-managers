# Manager Auth Package for Laravel

A secure middleware + token-based authentication system for manager-level access, built for microservice-based
architectures, using Laravel.

## 🌐 Overview

This package provides authentication protection for manager/admin-level routes using a two-step process:

1. **OAuth Authentication** through a centralized accounting microservice.
2. **Static Token Verification** using a hashed secret token stored in a `oauth_managers` table.

If unauthorized, the manager is redirected to the accounting service. After login, the manager must enter a static token
to verify identity.

---

## ✅ Features

- 🧩 **Configurable Laravel Middleware**
- 🔐 Supports **OAuth2 + static token**
- ⚠️ **Rate limiting** for incorrect attempts (configurable)
- 💾 Configurable caching (TTL, driver, prefix)
- 🧑‍💼 Artisan command: `manager:create` to create new manager records
- 🗂️ Includes multilingual support (EN/FA)
- 🗃️ Highly extensible and publishable

---

## 📦 Installation

```bash
composer require esanj/ms-package-managers
```

Run the install command to publish assets, and run migrations:

```bash
php artisan manager:install
```

## ⚙️ Configuration

Set the following environment variables:

```
MANAGER_SUCCESS_REDIRECT=/admin/dashboard
MANAGER_PUBLIC_KEY_PATH=storage/oauth-public.key
MANAGER_LOGO_PATH=/assets/vendor/manager/img/logo.png
```

## 🔑 Authentication Flow

protected route ( e . g . ) is behind .Your protected route (eg /admin) is behind CheckAuthManagerMiddleware.
not authenticated :If not authenticated:
to accounting microservice for OAuth loginRedirects to accounting microservice for OAuth login
return , it requests a static tokenUpon return, it requests a static token
Token is checked using a hashed comparison
Success? Manager is marked logged-in in the session

## 🔒 Middleware Protection

To protect routes:

```php
use Esanj\Manager\Middleware\CheckAuthManagerMiddleware;

Route::middleware([CheckAuthManagerMiddleware::class])
->prefix('admin')
->group(function () {
// Protected routes here
});
```

## 🔨 Artisan Commands

Create a new manager:

```bash
php artisan manager:create
```

You'll be asked for the manager ID. A random static token will be hashed and stored. Duplicate manager IDs are blocked.

## 🎯 Publishing Resources

You can publish any part of the package for customization:

Resource Command

Config:    ```php artisan vendor:publish --tag=manager-config```

Views: ```php artisan vendor:publish --tag=manager-views```

Lang files:    ```php artisan vendor:publish --tag=manager-lang```

Migrations:    ```php artisan vendor:publish --tag=manager-migrations```

Assets:    ```php artisan vendor:publish --tag=manager-assets```

## 💼 ManagerService Class Overview

Namespace: Esanj\Manager\Services\ManagerService
Purpose is the core application service responsible for handling manager - specific business logic . It acts as an abstraction layer between your application ( e.g. controllers , middleware ) and the persistence layer ( ) , following SOLID design principles .​​​The ManagerServiceis the core application service responsible for handling manager-specific business logic. It acts as an abstraction layer between your application (eg controllers, middleware) and the persistence layer ( ManagerRepository), following SOLID design principles .

Method Description

```findByManagerID(int $id)```	Fetches an Managerinstance by its manager ID (cached if enabled).

```checkManagerToken(Manager $manager, string $token): bool```	Validates a raw input tokenagainst a hashed token stored in the database.

```updateLastLogin(int $id)```	Updates the last_logintimestamp of a manager to now().

```updateManager(int $id, array $data)``` Manager	Updates a manager record. Accepts fields like token, is_active, etc.

```createManager(int $id, string $token)``` Manager	Creates a new manager with the given manager_idand a hashed token.

```switchToInactive(int $managerID)```	Flags the manager as inactive ( is_active= false).

```switchToActive(int $managerID)```	Flags the manager as active ( is_active= true).

Example Usage:
```php
use Esanj\Manager\Services\ManagerService;

$service = app(ManagerService::class);

$manager = $service->findByManagerID(175);

if ($service->checkManagerToken($manager, 'my-secret-token')) {
$service->updateLastLogin($manager->id);
}
```
**Notes**

are always hashed using Laravel’s for security .Tokens are always hashed using Laravel's `Hash::check()` for security.
This service is used internally in the middleware, controller, and artisan commands.
manager’s activation state ( ) is strictly checked before session persist .The manager's activation state ( is_active) is strictly checked before session persist.
