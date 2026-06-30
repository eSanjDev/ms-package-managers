# 🧩 Esanj Manager Package

A microservice‑friendly Laravel package that adds a secure, configurable **Manager (admin) panel** with
OAuth login delegated to the **Esanj Auth Bridge**, static/expirable access tokens, and granular,
role‑based permissions.

**Supports:** Laravel 10 · 11 · 12 · 13 — PHP 8.2+

---

## 🧠 Overview

`esanj/managers` plugs a complete **Manager** subsystem into your Laravel app:

- 👤 Manager accounts with roles (`admin`, `manager`, `operator`) and per‑manager permissions stored in the DB.
- 🔐 Login via the **Auth Bridge** (OAuth), with an optional per‑manager **static token** as a second factor.
- 🪪 Stateless **API access tokens** (HMAC‑signed) for the JSON API, plus session login for the web panel.
- 🧱 A ready‑made web panel (list / create / edit / activity log) and a matching JSON API.
- 🚦 Built‑in caching, rate‑limiting, configurable route prefixes, and activity logging.

> ℹ️ This package depends on **`esanj/auth-bridge`**, which performs the actual OAuth handshake. Configure it as
> well (its `ACCOUNTING_BRIDGE_*` env keys are listed below).

---

## ⚙️ Installation

```bash
composer require esanj/managers
```

Run the installer:

```bash
php artisan manager:install
```

The installer will:

1. Publish assets (`vendor:publish --tag=esanj-manager-assets`).
2. Optionally run `php artisan migrate`, then import default permissions (`manager:permissions-import`).
3. Append any missing `.env` keys (listed below) so you can fill them in.

Then create your first admin:

```bash
php artisan manager:create {esanj_id}
```

This prints a one‑time **static token** — copy it; it's stored hashed and can't be shown again.

---

## 🔐 Environment Variables

The installer ensures these keys exist in `.env`:

| Key                                  | Purpose                                                       |
|--------------------------------------|--------------------------------------------------------------|
| `ACCOUNTING_BRIDGE_CLIENT_ID`        | OAuth client ID (used by `esanj/auth-bridge`).               |
| `ACCOUNTING_BRIDGE_CLIENT_SECRET`    | OAuth client secret.                                         |
| `ACCOUNTING_BRIDGE_BASE_URL`         | Base URL of the Auth Bridge / Accounting service.           |
| `ACCOUNTING_BRIDGE_SUCCESS_REDIRECT` | Where the Bridge redirects after a successful OAuth login.  |
| `MANAGER_SUCCESS_REDIRECT`           | Where to send a manager after a successful panel login.     |
| `MANAGER_ACCESS_DENIED_REDIRECT`     | Where to send a manager who lacks permission.               |
| `MANAGER_PANEL_ROUTE_PREFIX`         | Prefix for the web panel routes (default: `admin`).         |
| `MANAGER_API_ROUTE_PREFIX`           | Prefix for the API routes (default: `api`).                 |

Other optional keys (all have sensible defaults — see [Configuration](#️-configuration)):
`MANAGER_AUTH_ROUTE_PREFIX`, `MANAGER_LOGO_PATH`, `MANAGER_ACCESS_TOKEN_TTL`, `MANAGER_TOKEN_LENGTH`,
`MANAGER_JUST_API`, `MANAGER_CACHE_ENABLED`, `MANAGER_CACHE_PREFIX`, `MANAGER_CACHE_TTL`,
`MANAGER_RATE_LIMIT_ENABLED`, `MANAGER_RATE_LIMIT_MAX_ATTEMPTS`, `MANAGER_RATE_LIMIT_DECAY_SECONDS`.

---

## ⚙️ Configuration

The config file is `src/config/manager.php`, merged under the key **`esanj.manager`** and publishable to
`config/esanj/manager.php`:

```bash
php artisan vendor:publish --tag=esanj-manager-config
```

| Key                              | Default                       | Description                                                       |
|----------------------------------|-------------------------------|-------------------------------------------------------------------|
| `logo_path`                      | `null`                        | Logo shown in the panel.                                          |
| `success_redirect`               | `/`                           | Redirect after a successful login.                               |
| `access_denied_redirect`         | `/`                           | Redirect when a manager lacks a permission (web).                |
| `access_token_expires_in`        | `1440` (minutes)              | Lifetime of an API access token.                                 |
| `token_length`                   | `128`                         | Max length accepted for a static token (validation).            |
| `just_api`                       | `false`                       | API‑only mode — disables the web panel, views, and web routes.  |
| `routes.auth_prefix`             | `admin`                       | Prefix for the web **auth** routes (`/token`, `/logout`).       |
| `routes.panel_prefix`            | `admin`                       | Prefix for the web **panel** routes (manager CRUD).             |
| `routes.api_prefix`              | `api`                         | Prefix for **all** API routes.                                  |
| `middlewares.api`                | `['api', 'manager.auth:api']` | Middleware stack for API panel routes.                          |
| `middlewares.web`                | `['web', 'manager.auth:web']` | Middleware stack for web panel routes.                          |
| `cache.is_enabled`               | `true`                        | Cache manager lookups.                                          |
| `cache.prefix`                   | `manager_`                    | Cache key prefix.                                               |
| `cache.driver`                   | `env('CACHE_STORE','file')`   | Cache store used for manager data.                             |
| `cache.ttl`                      | `10080` (7 days, minutes)     | Manager cache TTL.                                             |
| `rate_limit.is_enabled`          | `true`                        | Throttle the auth/login endpoints.                            |
| `rate_limit.max_attempts`        | `10`                          | Allowed attempts before `429`.                               |
| `rate_limit.decay_seconds`       | `600`                         | Window for the attempt counter.                              |
| `permissions`                    | 4 manager permissions         | Default permissions imported into the DB.                    |
| `access_provider`                | action → permission map       | Which permission each controller action requires.            |
| `extra_field`                    | `[]`                          | Blade components injected into the edit panel.               |

### Default permissions

```php
'permissions' => [
    'managers.list'   => ['display_name' => 'List Managers',   'description' => '...'],
    'managers.create' => ['display_name' => 'Create Manager',  'description' => '...'],
    'managers.edit'   => ['display_name' => 'Edit Managers',   'description' => '...'],
    'managers.delete' => ['display_name' => 'Delete Managers', 'description' => '...'],
],
```

Imported into the `permissions` table with `php artisan manager:permissions-import`.

---

## 🧰 Artisan Commands

| Command                          | Description                                                                 |
|----------------------------------|-----------------------------------------------------------------------------|
| `manager:install`                | Publishes assets, ensures `.env` keys, optionally migrates & imports perms. |
| `manager:create {esanj_id?}`     | Creates an **admin** manager and prints a one‑time static token.            |
| `manager:permissions-import`     | Upserts the permissions from `config('esanj.manager.permissions')` into DB. |

---

## 🔐 Authentication Flows

### API flow

| Step | Request                                            | Body / Query        | Returns                                              |
|------|----------------------------------------------------|---------------------|-----------------------------------------------------|
| 1    | `GET  /{api_prefix}/managers/redirect`             | —                   | `{ data: { redirect_url } }` — send the user there. |
| 2    | `POST /{api_prefix}/managers/verify`               | `{ code }`          | `{ data: { requires_token, auth_code } }`           |
| 3    | `POST /{api_prefix}/managers/authenticate`         | `{ auth_code, token? }` | `{ data: { access_token, token_type, expires_in } }` |
| 4    | Call the API with `Authorization: Bearer {access_token}` | —             | —                                                   |

- `code` (step 2) is the authorization code the Auth Bridge gives the user after OAuth login.
- `requires_token` tells you whether this manager needs to supply their static `token` in step 3.
- The `access_token` is an HMAC‑signed token (not a JWT), valid for `access_token_expires_in` minutes.

### Web flow

1. Unauthenticated managers are redirected to the Auth Bridge (`auth-bridge.redirect`).
2. After OAuth, `GET /{auth_prefix}/managers/token` either logs them in (no static token needed) or shows the
   token form.
3. `POST /{auth_prefix}/managers/token` validates the static token and logs them into the `manager` session guard.
4. `POST /{auth_prefix}/managers/logout` logs out.

---

## 🌐 API Endpoints

All under the `api_prefix` (default `api`) and guarded by `manager.auth:api` (Bearer token) plus per‑action
permission checks.

| Method   | URI                                                | Description                                          | Permission        |
|----------|----------------------------------------------------|------------------------------------------------------|-------------------|
| `GET`    | `/{api_prefix}/managers`                           | Paginated list (supports `search`, `only_trash`).    | `managers.list`   |
| `POST`   | `/{api_prefix}/managers`                           | Create a manager (+ sync `permissions` by key).      | `managers.create` |
| `GET`    | `/{api_prefix}/managers/{manager}`                 | Show a manager (includes its `permissions`).         | `managers.list`   |
| `PUT`    | `/{api_prefix}/managers/{manager}`                 | Update a manager (+ sync `permissions` by key).      | `managers.edit`   |
| `DELETE` | `/{api_prefix}/managers/{manager}`                 | Soft‑delete a manager.                               | `managers.delete` |
| `POST`   | `/{api_prefix}/managers/{id}/restore`              | Restore a soft‑deleted manager (`404` if not found). | `managers.delete` |
| `GET`    | `/{api_prefix}/managers/{manager}/meta/{key}`      | Read one meta value (`404` if unset).                | `managers.list`   |
| `POST`   | `/{api_prefix}/managers/{manager}/meta`            | Create/update a meta key/value.                      | `managers.list`   |
| `GET`    | `/{api_prefix}/managers/{manager}/activities`      | Paginated activity log (supports `search`).          | `managers.list`   |
| `GET`    | `/{api_prefix}/managers/{manager}/activities/{id}` | One activity entry.                                  | `managers.list`   |

> With defaults that means `/api/managers`, `/api/managers/{manager}`, etc.

---

## 🛡️ Middleware & Authorization

The package registers two middleware aliases:

| Alias                       | Class                              | Purpose                                                      |
|-----------------------------|------------------------------------|-------------------------------------------------------------|
| `manager.auth:{web\|api}`   | `CheckAuthManagerMiddleware`       | Authenticates the manager (session for `web`, Bearer for `api`). |
| `manager.permission:{key}`  | `CheckManagerPermissionMiddleware` | Requires the given permission key.                          |

It also defines a **Gate** for every permission key, so Blade `@can` and `Gate` checks work out of the box:

```php
// Routes
Route::middleware(['manager.auth:web', 'manager.permission:managers.edit'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::middleware('manager.auth:api')->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
});
```

```blade
@can('managers.edit')
    <a href="...">Edit</a>
@endcan
```

> Admins (`role = admin`) implicitly pass **all** permission checks.

---

## 📚 Documentation

For a complete, beginner‑friendly, step‑by‑step walkthrough — installing, the auth flows explained simply,
**adding a new permission**, **protecting your own page**, **injecting extra fields into the edit panel**,
API‑only mode, customizing views/translations, and troubleshooting — see **[docs/GUIDE.md](docs/GUIDE.md)**.

---

## 📜 License

Released under the **MIT License**.