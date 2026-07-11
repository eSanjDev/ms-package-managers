# 📚 Esanj Manager — Complete Beginner's Guide

This guide assumes **you have never used this package before**. It explains everything in plain language, step by
step, with copy‑paste commands. If you can edit a `.env` file and add a route, you can follow along.

> 💡 **What is this package?** It gives your Laravel app an **admin (Manager) area**: a login secured by the Esanj
> Auth Bridge, manager accounts with roles and permissions, a ready‑made web panel, and a JSON API — all
> configurable. You don't build the login or the permission system yourself; you just configure and extend it.

---

## Table of Contents

1. [The big picture](#1-the-big-picture)
2. [Requirements & dependencies](#2-requirements--dependencies)
3. [Installation, step by step](#3-installation-step-by-step)
4. [Filling in your `.env`](#4-filling-in-your-env)
5. [Creating your first manager](#5-creating-your-first-manager)
6. [How login works (web vs API)](#6-how-login-works-web-vs-api)
7. [Roles & permissions explained](#7-roles--permissions-explained)
8. [Protecting your own pages & routes](#8-protecting-your-own-pages--routes)
9. [Recipe: add a new permission](#9-recipe-add-a-new-permission)
10. [Recipe: add an extra field to the edit panel](#10-recipe-add-an-extra-field-to-the-edit-panel)
11. [Recipe: run in API‑only mode](#11-recipe-run-in-api-only-mode)
12. [Recipe: customize views, translations, assets, and the logo](#12-recipe-customize-views-translations-assets-and-the-logo)
13. [Per‑manager data: meta & activity log](#13-per-manager-data-meta--activity-log)
14. [Using the `Manager` facade](#14-using-the-manager-facade)
15. [Caching & rate limiting](#15-caching--rate-limiting)
16. [Configuration reference](#16-configuration-reference)
17. [Route reference](#17-route-reference)
18. [Troubleshooting](#18-troubleshooting)
19. [Cheat sheet](#19-cheat-sheet)

---

## 1. The big picture

You mostly interact with a few concepts:

| Thing            | What it is                                                                            |
|------------------|---------------------------------------------------------------------------------------|
| **Manager**      | An admin account. Has an `esanj_id`, a `role`, an optional static `token`, and permissions. |
| **Role**         | One of `admin`, `manager`, `operator`. `admin` automatically has **every** permission. |
| **Permission**   | A named capability like `managers.edit`, stored in the DB and checked via Gates/middleware. |
| **Auth Bridge**  | A separate Esanj package that performs the OAuth login. This package delegates login to it. |
| **Static token** | An optional per‑manager secret that acts like a second factor at login time.          |
| **Access token** | A signed token your API clients send as `Authorization: Bearer ...`.                  |

Internally (you rarely touch these): a **Service** layer (`ManagerService`, `ManagerAuthService`) holds the logic,
a **Repository** (`ManagerRepository`) caches DB lookups, **Middleware** guards routes, and **Controllers** expose
the web panel and JSON API.

---

## 2. Requirements & dependencies

- **PHP** 8.2+
- **Laravel** 10, 11, 12, or 13
- **`esanj/auth-bridge`** — installed automatically as a dependency. It handles the OAuth handshake, so you must
  configure its `ACCOUNTING_BRIDGE_*` env values too (see [section 4](#4-filling-in-your-env)).

---

## 3. Installation, step by step

**Step 1 — require the package:**

```bash
composer require esanj/managers
```

**Step 2 — run the installer:**

```bash
php artisan manager:install
```

It will:
1. Publish the package **assets** to `resources/assets/packages/manager`.
2. Ask *"Should migrations be performed?"* — answer **yes** the first time. This creates the `managers`,
   `permissions`, `manager_permissions`, `manager_metas`, and `manager_activities` tables, then imports the
   default permissions.
3. Add any missing keys to your `.env`.

**Step 3 — fill in `.env`** (next section), then you're ready.

---

## 4. Filling in your `.env`

After install, open `.env` and set at least these (ask your Esanj admin for the Bridge values):

```env
# Auth Bridge (OAuth) — required for login to work
ACCOUNTING_BRIDGE_CLIENT_ID=your-client-id
ACCOUNTING_BRIDGE_CLIENT_SECRET=your-client-secret
ACCOUNTING_BRIDGE_BASE_URL=https://accounting.your-domain.com
ACCOUNTING_BRIDGE_SUCCESS_REDIRECT=https://your-app.com/admin/managers/token

# Where to send managers in your app
MANAGER_SUCCESS_REDIRECT=/admin/managers
MANAGER_ACCESS_DENIED_REDIRECT=/

# Route prefixes (optional — these are the defaults)
MANAGER_PANEL_ROUTE_PREFIX=admin
MANAGER_API_ROUTE_PREFIX=api
```

> ⚠️ After changing `.env` or config, run `php artisan config:clear`.

---

## 5. Creating your first manager

There's no "sign up" — you create the first admin from the command line:

```bash
php artisan manager:create 1001
```

(`1001` is the **Esanj ID** of the account in the Auth Bridge / Accounting service.) The command prints something
like:

```
Token: 9f8b1c2d3e4f5a6b7c8d9e0f1a2b3c4d
Manager successfully created!
```

- That **token** is the manager's static token. It is shown **once** and stored **hashed** — copy it now.
- The new manager gets the `admin` role, so they can do everything.

You can omit the ID to be prompted for it: `php artisan manager:create`.

---

## 6. How login works (web vs API)

You don't write the login — the package and the Auth Bridge handle it. Here's what happens so you understand it.

### Web panel login

1. A manager opens a protected panel page. If not logged in, they're redirected to the **Auth Bridge** to do the
   OAuth login.
2. The Bridge sends them back to `/{auth_prefix}/managers/token` (default `/admin/managers/token`).
3. If the manager has **no static token requirement**, they're logged in immediately and sent to
   `MANAGER_SUCCESS_REDIRECT`. Otherwise they see a form asking for their **static token**.
4. Submitting the correct token logs them into the `manager` session guard.

### API login (for SPAs / mobile / services)

| Step | Call                                              | You send             | You get back                                       |
|------|---------------------------------------------------|----------------------|----------------------------------------------------|
| 1    | `GET /api/managers/redirect`                      | —                    | `redirect_url` → open it so the user logs in.      |
| 2    | `POST /api/managers/verify`                       | `{ "code": "..." }`  | `{ requires_token, auth_code }`                     |
| 3    | `POST /api/managers/authenticate`                 | `{ "auth_code": "...", "token": "..." }` | `{ access_token, token_type, expires_in }` |
| 4    | Any `/api/managers...` call                       | header `Authorization: Bearer {access_token}` | the data (+ a renewed‑token header when needed) |

- `code` in step 2 is the authorization code the Bridge gave the user.
- `requires_token` tells you whether to ask the user for their static `token` in step 3 (omit it if `false`).
- The `access_token` is valid for `access_token_expires_in` minutes (default **15 min** — deliberately short).

**Silent renewal — no refresh token, no refresh route.** The access token secretly carries the manager's
**accounting** refresh token (encrypted). When you hit any protected endpoint with an expired access token, the
middleware asks accounting to refresh it: if accounting still accepts the manager, a new access token is issued
automatically and returned in the `X-Manager-Access-Token` (and `X-Manager-Token-Expires-In`) response header —
your request still succeeds. If accounting has **blocked/restricted** the manager, the refresh fails and you get
`401`. That means a block at accounting removes API access within one 15‑minute cycle, and no refresh token can
revive it. The only client‑side duty: when a response includes `X-Manager-Access-Token`, store it and use it next
time (accounting rotates the underlying refresh token, so the previous one won't work twice).

> 🔒 Steps 3 and the web token form are **rate‑limited** (default: 10 attempts / 10 minutes per IP) to slow down
> brute‑forcing the static token.

---

## 7. Roles & permissions explained

- **Roles:** `admin`, `manager`, `operator`. An **`admin` passes every permission check automatically**. The other
  roles only have the permissions you explicitly attach to them.
- **Permissions:** named strings (e.g. `managers.edit`) stored in the `permissions` table. The package ships four
  by default (`managers.list/create/edit/delete`).
- **Two ways to check a permission** — both respect the rules above:

  **Middleware** (protect a whole route):
  ```php
  Route::middleware('manager.permission:managers.edit')->group(fn () => /* ... */);
  ```

  **Gate / Blade** (`@can`), because the package registers a Gate for every permission key:
  ```blade
  @can('managers.delete')
      <button>Delete</button>
  @endcan
  ```
  ```php
  if (Gate::allows('managers.edit')) { /* ... */ }
  ```

### Assigning permissions to a manager

Permissions are attached to a manager by their **`key`** (e.g. `managers.edit`), not by their numeric id. This
applies to both the web panel form and the JSON API:

- **Web panel:** tick the permission checkboxes on the **create/edit** screen — each checkbox submits a key.
- **API:** send a `permissions` array of keys when creating or updating a manager. The keys must already exist in
  the `permissions` table (validated with `exists:permissions,key`), and the package **syncs** them — the list you
  send fully replaces the manager's current permissions.

  ```jsonc
  // POST /api/managers  or  PUT /api/managers/{manager}
  {
      "esanj_id": 1001,
      "name": "Jane",
      "role": "operator",
      "permissions": ["managers.list", "managers.edit"]
  }
  ```

> `admin` managers pass every check regardless, so `permissions` is optional for them and required for the other
> roles. When you load a manager (e.g. `GET /api/managers/{manager}`), its `permissions` are returned as objects
> with `key`, `display_name`, and `description`.

---

## 8. Protecting your own pages & routes

Combine the two middleware aliases the package provides:

- `manager.auth:web` (or `:api`) — "the user must be a logged‑in manager."
- `manager.permission:{key}` — "...and must have this permission."

**A web page only admins/editors can see:**

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['manager.auth:web', 'manager.permission:managers.edit'])
    ->get('/admin/reports', [ReportController::class, 'index']);
```

**An API endpoint protected by a Bearer token:**

```php
Route::middleware(['manager.auth:api', 'manager.permission:managers.list'])
    ->get('/api/reports', [ReportApiController::class, 'index']);
```

> The currently authenticated manager is available via `auth('manager')->user()` (or `Auth::guard('manager')`).

---

## 9. Recipe: add a new permission

Say you're building a "Reports" area and want a `reports.view` permission.

**Step 1 — publish the config** (once), so you can edit it:

```bash
php artisan vendor:publish --tag=esanj-manager-config
```

**Step 2 — add your permission** in `config/esanj/manager.php`:

```php
'permissions' => [
    // ...existing entries...
    'reports.view' => [
        'display_name' => 'View Reports',
        'description'  => 'Allows viewing the reports area',
    ],
],
```

**Step 3 — import it into the database:**

```bash
php artisan manager:permissions-import
```

This is safe to re‑run any time — it **upserts** (updates or creates) each permission by its key.

**Step 4 — use it** anywhere:

```php
Route::middleware(['manager.auth:web', 'manager.permission:reports.view'])->get('/admin/reports', ...);
```
```blade
@can('reports.view') ... @endcan
```

Now, in the manager **edit** panel, this permission appears under the **"reports"** group automatically (the panel
groups permissions by the text before the first dot in the key).

---

## 10. Recipe: add an extra field to the edit panel

The edit screen can render your own Blade partials at the bottom — handy for app‑specific settings (limits, notes,
toggles) without touching the package's views.

**Step 1 — create a Blade partial**, e.g. `resources/views/admin/manager/limit.blade.php`. The current
`$manager` model is in scope:

```blade
<div class="card mt-3">
    <div class="card-body">
        <h5>Extra settings for {{ $manager->name }}</h5>
        {{-- your fields here --}}
    </div>
</div>
```

**Step 2 — register it** in `config/esanj/manager.php` using its view name (dot notation):

```php
'extra_field' => [
    'admin.manager.limit',
],
```

That partial now renders below the edit form for every manager. Add as many as you like.

---

## 11. Recipe: run in API‑only mode

If your frontend is a separate SPA/mobile app and you don't want the bundled web panel at all:

```env
MANAGER_JUST_API=true
```

This disables the web routes and views entirely; only the JSON API (`/api/managers...`) is registered. Clear your
config cache afterwards (`php artisan config:clear`).

---

## 12. Recipe: customize views, translations, assets, and the logo

Publish only the parts you want to change (each has its own tag):

```bash
php artisan vendor:publish --tag=esanj-manager-views    # → resources/views/vendor/manager
php artisan vendor:publish --tag=esanj-manager-lang     # → lang/vendor/manager
php artisan vendor:publish --tag=esanj-manager-assets   # → resources/assets/packages/manager
php artisan vendor:publish --tag=esanj-manager-config   # → config/esanj/manager.php
php artisan vendor:publish --tag=esanj-manager-migrations
```

- **Views:** edit the files under `resources/views/vendor/manager/` (e.g. `panel/index.blade.php`,
  `panel/edit.blade.php`, `auth/token.blade.php`). Your copies override the package's.
- **Translations:** the package ships English (`en`) and Persian (`fa`) in `lang/vendor/manager/`. Edit those or
  add a new locale folder with a `manager.php` file of the same shape.
- **Logo:** set `MANAGER_LOGO_PATH` in `.env` (or `logo_path` in config) to your own image URL/path.

> ⚠️ Always edit the **published** copies in your app — never the files under `vendor/`, which Composer overwrites
> on update.

---

## 13. Per‑manager data: meta & activity log

**Meta** = arbitrary key/value data attached to a manager (think "profile extras"):

```php
$manager->setMeta('phone', '+98912...');   // create or update
$meta = $manager->getMeta('phone');         // returns the ManagerMeta model (or null)
echo $meta->value;
```

Via the API:
- `POST /api/managers/{manager}/meta` with `{ "key": "...", "value": "..." }`
- `GET  /api/managers/{manager}/meta/{key}`

**Activity log** = an audit trail. The package automatically logs `manager.created/updated/deleted/restored/login`.
Each entry stores the actor, IP, and user agent. You can add your own entries:

```php
// For the currently logged-in manager (records actor automatically):
app(\Esanj\Manager\Services\ManagerService::class)->setActivity('report.exported', ['report_id' => 42]);

// For a specific manager:
$service->logActivityFor($manager, 'note.added', ['note' => 'VIP']);
```

Read them: `GET /api/managers/{manager}/activities` (paginated, supports `?search=`).

---

## 14. Using the `Manager` facade

The `Esanj\Manager\Facades\Manager` facade proxies to `ManagerService`, so you can call its methods statically:

```php
use Esanj\Manager\Facades\Manager;

Manager::findByEsanjId(1001);
Manager::hasPermission($managerId, 'managers.edit');
Manager::getManagersWithPaginate();
```

> Tip: prefer dependency injection (`public function __construct(ManagerService $service)`) in classes you control;
> use the facade for quick calls in Blade or simple closures.

---

## 15. Caching & rate limiting

- **Caching:** manager lookups (`findById`, `findByEsanjId`) are cached to avoid repeat DB hits. The cache is
  **cleared automatically** whenever a manager is created/updated/deleted/restored, so you normally don't manage it.
  Control it via `cache.is_enabled`, `cache.driver`, `cache.ttl`, `cache.prefix`.
- **Rate limiting:** the login/authenticate endpoints are throttled per IP. Tune via `rate_limit.is_enabled`,
  `rate_limit.max_attempts`, and `rate_limit.decay_seconds`. Exceeding the limit returns `429`.

---

## 16. Configuration reference

File: `config/esanj/manager.php` (read internally as `esanj.manager`).

| Key                        | Default                       | Env                                   | Description                                   |
|----------------------------|-------------------------------|---------------------------------------|-----------------------------------------------|
| `logo_path`                | `null`                        | `MANAGER_LOGO_PATH`                   | Panel logo image.                             |
| `success_redirect`         | `/`                           | `MANAGER_SUCCESS_REDIRECT`            | Redirect after login.                         |
| `access_denied_redirect`   | `/`                           | `MANAGER_ACCESS_DENIED_REDIRECT`      | Redirect on missing permission (web).         |
| `access_token_expires_in`  | `15`                          | `MANAGER_ACCESS_TOKEN_TTL`            | API access‑token lifetime (minutes; auto‑renewed against accounting). |
| `token_length`             | `128`                         | `MANAGER_TOKEN_LENGTH`                | Max static‑token length (validation).         |
| `just_api`                 | `false`                       | `MANAGER_JUST_API`                    | API‑only mode.                                |
| `routes.auth_prefix`       | `admin`                       | `MANAGER_AUTH_ROUTE_PREFIX`           | Web auth routes prefix.                       |
| `routes.panel_prefix`      | `admin`                       | `MANAGER_PANEL_ROUTE_PREFIX`          | Web panel routes prefix.                      |
| `routes.api_prefix`        | `api`                         | `MANAGER_API_ROUTE_PREFIX`            | API routes prefix.                            |
| `middlewares.api`          | `['api','manager.auth:api']`  | —                                     | API route middleware.                         |
| `middlewares.web`          | `['web','manager.auth:web']`  | —                                     | Web route middleware.                         |
| `cache.is_enabled`         | `true`                        | `MANAGER_CACHE_ENABLED`               | Enable manager caching.                       |
| `cache.prefix`             | `manager_`                    | `MANAGER_CACHE_PREFIX`                | Cache key prefix.                             |
| `cache.driver`             | `file`                        | `CACHE_STORE`                         | Cache store.                                  |
| `cache.ttl`                | `10080`                       | `MANAGER_CACHE_TTL`                   | Cache TTL (minutes, 7 days).                  |
| `rate_limit.is_enabled`    | `true`                        | `MANAGER_RATE_LIMIT_ENABLED`          | Enable throttling.                            |
| `rate_limit.max_attempts`  | `10`                          | `MANAGER_RATE_LIMIT_MAX_ATTEMPTS`     | Attempts before `429`.                        |
| `rate_limit.decay_seconds` | `600`                         | `MANAGER_RATE_LIMIT_DECAY_SECONDS`    | Throttle window (seconds).                    |
| `permissions`              | 4 defaults                    | —                                     | Permissions imported into the DB.             |
| `access_provider`          | action → permission map       | —                                     | Permission required by each controller action.|
| `extra_field`              | `[]`                          | —                                     | Blade views injected into the edit panel.     |

---

## 17. Route reference

With default prefixes (`auth`/`panel` = `admin`, `api` = `api`):

**Web — auth** (only when `just_api = false`):
| Method | URI                      | Name                    |
|--------|--------------------------|-------------------------|
| GET    | `/admin/managers/token`  | `managers.auth.index`   |
| POST   | `/admin/managers/token`  | `managers.auth.login`   |
| POST   | `/admin/managers/logout` | `managers.auth.logout`  |

**Web — panel** (only when `just_api = false`):
| Method      | URI                                          | Name                     |
|-------------|----------------------------------------------|--------------------------|
| GET         | `/admin/managers`                            | `managers.index`         |
| GET         | `/admin/managers/create`                     | `managers.create`        |
| POST        | `/admin/managers`                            | `managers.store`         |
| GET         | `/admin/managers/{manager}/edit`             | `managers.edit`          |
| PUT/PATCH   | `/admin/managers/{manager}`                  | `managers.update`        |
| DELETE      | `/admin/managers/{manager}`                  | `managers.destroy`       |
| POST        | `/admin/managers/{manager}/restore`          | `managers.restore`       |
| GET         | `/admin/managers/{manager}/activities`       | `managers.activities`    |
| GET         | `/admin/managers/{manager}/activities/{id}`  | `managers.activities.log`|

**API** (always registered): see the [API Endpoints table in the README](../README.md#-api-endpoints).

---

## 18. Troubleshooting

**Login redirects in a loop / never authenticates.**
Your `ACCOUNTING_BRIDGE_*` values are wrong or the Bridge is unreachable. Verify all four keys and that
`ACCOUNTING_BRIDGE_SUCCESS_REDIRECT` points back to `/{auth_prefix}/managers/token`. Then `php artisan config:clear`.

**`403` / "Access denied" for a manager who should have access.**
They lack the permission. Either attach it on their **edit** page, or give them the `admin` role (admins bypass all
checks). Remember new permissions must be added to config **and** imported with `manager:permissions-import`.

**My new permission doesn't appear in the panel.**
You added it to config but didn't import it. Run `php artisan manager:permissions-import`, then reload the edit page.

**Changes to config/views/translations don't show up.**
Run `php artisan config:clear` (and `view:clear`). Make sure you edited the **published** copies, not `vendor/`.

**`429 Too Many Requests` while testing login.**
That's the rate limiter. Wait for the window (`rate_limit.decay_seconds`), or temporarily set
`MANAGER_RATE_LIMIT_ENABLED=false` in your local `.env`.

**I lost a manager's static token.**
Tokens are stored hashed and can't be recovered. Set a new one via the manager **edit** page (or API `PUT`), or
recreate the manager.

---

## 19. Cheat sheet

```bash
# Install & set up
composer require esanj/managers
php artisan manager:install
php artisan manager:create {esanj_id}        # creates an admin, prints a one-time token
php artisan manager:permissions-import       # after editing config permissions

# Publish parts to customize
php artisan vendor:publish --tag=esanj-manager-config
php artisan vendor:publish --tag=esanj-manager-views
php artisan vendor:publish --tag=esanj-manager-lang
php artisan vendor:publish --tag=esanj-manager-assets

php artisan config:clear                     # after any config/.env change
```

```php
// Protect routes
Route::middleware(['manager.auth:web', 'manager.permission:managers.edit'])->group(/* ... */);
Route::middleware('manager.auth:api')->group(/* ... */);

// Authorization checks
@can('managers.edit') ... @endcan
Gate::allows('managers.delete');

// Current manager
auth('manager')->user();

// Service / facade
Manager::hasPermission($id, 'managers.list');
$manager->setMeta('key', 'value');
$manager->getMeta('key');
```

| I want to...                          | Do this                                                         |
|---------------------------------------|----------------------------------------------------------------|
| Add an admin                          | `php artisan manager:create {esanj_id}`                         |
| Add a permission                      | edit `config/esanj/manager.php` → `manager:permissions-import`  |
| Protect a page                        | `manager.auth:web` + `manager.permission:{key}` middleware      |
| Add fields to the edit screen         | `extra_field` config → a Blade partial                          |
| Disable the web panel                 | `MANAGER_JUST_API=true`                                         |
| Change the logo                       | `MANAGER_LOGO_PATH=...`                                         |
| Restyle the panel                     | publish `esanj-manager-views` / `esanj-manager-assets`          |

---

Need the quick reference instead? See the [README](../README.md).