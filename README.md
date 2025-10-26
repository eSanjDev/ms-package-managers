# 🧩 Esanj Manager Package

A microservice‑friendly Laravel 12 package providing a secure and configurable Manager panel with OAuth bridge
authentication, and granular permission control.

---

## 🧠 Overview

`esanj/managers` integrates a **Manager** system into microservice ecosystems built around Laravel ≥ 12.  
It delegates **OAuth** login to the *Accounting Bridge Service*, issues **static and expirable access tokens**, and
provides clean interfaces to manage users, roles, and permissions.

Features:

- ✅ Manager roles & permissions stored in DB
- ✅ Token‑based API & web authentication
- ✅ Rate‑limit, caching, and configurable route prefixes
- ✅ Commands to install, migrate, and import permissions
- ✅ SOLID, Clean Code, and security‑focused baseline

---

## ⚙️ Installation

Install via Composer:

```bash
composer require esanj/managers
```

Run the installer:

```bash
php artisan manager:install
```

This will:

1. Publish assets/config via `vendor:publish` (tag `esanj-manager-assets`),
2. Optionally run `php artisan migrate`,
3. Import default permissions with `manager:permissions-import`,
4. Ensure necessary `.env` keys exist (see below).

---

## 🔐 Environment Variables

The installer (`InstallCommand.php`) ensures each of these keys exists in `.env`:

| Key                                  | Purpose                                           |
|--------------------------------------|---------------------------------------------------|
| `ACCOUNTING_BRIDGE_CLIENT_ID`        | OAuth client ID for Accounting Bridge.            |
| `ACCOUNTING_BRIDGE_CLIENT_SECRET`    | OAuth secret.                                     |
| `ACCOUNTING_BRIDGE_BASE_URL`         | URL of the Accounting Bridge server.              |
| `ACCOUNTING_BRIDGE_SUCCESS_REDIRECT` | Redirect after OAuth success.                     |
| `MANAGER_SUCCESS_REDIRECT`           | Redirect in your app after login success.         |
| `MANAGER_ACCESS_DENIED_REDIRECT`     | Redirect when user lacks permission.              |
| `MANAGER_PANEL_ROUTE_PREFIX`         | Prefix for manager web routes (default: `admin`). |
| `MANAGER_API_ROUTE_PREFIX`           | Prefix for manager API routes (default: `api`).   |

---

## ⚙️ Configuration File

`src/config/manager.php` (published to `config/esanj/manager.php`) defines:

### 🔩 Behavioral options

```php
'token_length' => 32,
'access_token_expires_in' => 1440, // 24 h
'just_api' => env('MANAGER_JUST_API', false),
```

### 🔒 Middlewares

```php
'middlewares' => [
  'api' => ['api', 'auth.api'],
  'web' => ['web', 'auth.manager'],
],
```

### 🚦 Rate Limit and Cache

```php
'rate_limit' => [
  'max_attempts' => 10,
  'decay_seconds' => 600,
],
'cache' => [
  'driver' => 'file',
  'ttl' => 60 * 24 * 7
],
```

### 🎯 Default Permissions

```php
"permissions" => [
  'managers.edit'   => [ 'display_name' => 'Edit Managers' ],
  'managers.list'   => [ 'display_name' => 'List Managers' ],
  'managers.create' => [ 'display_name' => 'Create Manager' ],
  'managers.delete' => [ 'display_name' => 'Delete Managers' ],
],
```

These are imported into the database using the `manager:permissions-import` command.

---

## 🧰 Artisan Commands

| Command                          | Description                                                               | Key Details                                                            |
|----------------------------------|---------------------------------------------------------------------------|------------------------------------------------------------------------|
| **`manager:install`**            | Publishes assets, validates `.env`, runs migrations, imports permissions. | Uses `ensureEnvKeys()` to safely append keys.                          |
| **`manager:create`**             | Creates new manager with static token.                                    | Generates 32‑char random token (`Str::random(32)`); asks for Esanj ID. |
| **`manager:permissions-import`** | Reads `config('esanj.manager.permissions')`, updates DB.                  | Uses `Permission::updateOrCreate`.                                     |

All command outputs are colorized and return appropriate exit codes.

---

## 🔐 Authentication & API Flow

### 1️⃣ Redirect to OAuth

```
GET /{prefix}/redirect
```

→ returns redirect URL for Accounting Bridge login.

### 2️⃣ Callback & Session Token

After successful OAuth, the Bridge redirects back; the session stores:

```
auth_bridge.access_token
```

### 3️⃣ Token Verification

```
GET /{prefix}/verify?code={auth_bridge.access_token}
```

Response indicates whether a static token is required (`requires_token` flag).

### 4️⃣ Exchange for Manager Access‑Token

```
POST /{prefix}/authenticate
```

Body:

```json
{
    "code": "{auth_bridge.access_token}",
    "token": "{optional-static-token}"
}
```

Returns manager‑API token respecting `access_token_expires_in` from config.

### 5️⃣ API Usage

Use header:

```
Authorization: Bearer {access_token}
```


All endpoints are located within the prefix defined in your config  
(`config('manager.routes.api_prefix')`, default: `/api`) + `/admin/managers`  
and are guarded by `auth.api` middleware (Bearer token required).

| Method | URI | Description / Behavior |
|:-------|:----|:-----------------------|
| **GET** | `/api/admin/managers` | Retrieve paginated list of all managers (active + optional trashed). |
| **POST** | `/api/admin/managers` | Create a new manager record (name, email, role, token). |
| **GET** | `/api/admin/managers/{manager}` | Get details of a specific manager including permissions and meta. |
| **PUT** | `/api/admin/managers/{manager}` | Update manager’s profile, email, status, or permissions. Automatically syncs permissions. |
| **DELETE** | `/api/admin/managers/{manager}` | Soft‑delete a manager record. |
| **POST** | `/api/admin/managers/{id}/restore` | Restore a previously soft‑deleted manager. Returns 404 if not found. |
| **GET** | `/api/admin/managers/regenerate` | Generate a new static token (uses `token_length` from config). |
| **GET** | `/api/admin/managers/{manager}/meta/{key}` | Retrieve a single meta key for given manager. Returns 404 if not set. |
| **POST** | `/api/admin/managers/{manager}/meta` | Store or update a meta key/value pair for that manager. |
| **GET** | `/api/admin/managers/{manager}/activities` | Return paginated activity logs for manager. Supports `search` by type or meta. |
| **GET** | `/api/admin/managers/{manager}/activities/{activity}` | Return a single activity entry with full metadata. Used by UI modal viewer. |

---

### 🔐 Middlewares

Middleware aliases used across the **Esanj** services to secure routes and APIs.

| Alias | Class | Purpose |
|:------|:-------------------------------------------|:--------------------------------------------------------------|
| `auth.manager` | `CheckAuthManagerMiddleware` | Ensures that a manager is authenticated before accessing protected routes. |
| `auth.api` | `AuthenticateTokenMiddleware` | Validates API authentication tokens for secure API requests. |
| `permission` | `CheckManagerPermissionMiddleware` | Checks if the current manager has the required permissions to access a route. |

**Usage Example:**


```php
// Web routes protected by manager authentication and permission checks
Route::middleware(['auth.manager', 'permission:manage-users'])->group(function () {
   Route::get('/dashboard', [DashboardController::class, 'index']);
});

// API routes protected by token authentication
Route::middleware('auth.api')->group(function () {
    Route::get('/api/data', [ApiController::class, 'fetch']);
});
```

### 🧩 Using `@can` for Authorization

The **Esanj Manager Package** provides a full role‑based and permission‑based authorization layer on top of Laravel’s native `Gate` system.  
At the Blade level, all **permission checks** are made using the `@can` directive, but internally these checks respect the **Manager guard** and **manager‑specific permissions** defined in `config/esanj/manager.php`.

---

## 📜 License

This package is released under the **MIT License**.
