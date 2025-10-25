# 🧑‍💼 Manager Package for Laravel

[![Latest Version](https://img.shields.io/packagist/v/esanj/ms-package-managers.svg?style=flat-square)](https://packagist.org/packages/esanj/ms-package-managers)
[![License](https://img.shields.io/packagist/l/esanj/ms-package-managers.svg?style=flat-square)](LICENSE)

> 🔐 A simple and secure **OAuth-based Manager Panel Authentication** system for Laravel 12 — built with **SOLID principles**, and manager permission control.

---

## ✨ Features

✅ OAuth-based authentication via Accounting Bridge  
✅ Manager role & permission management  
✅ Dedicated route prefixes for panel & API  
✅ Web UI for managing managers  
✅ Admin role with full access   
✅ Auth & Permission middlewares  
✅ Artisan command to create admin managers  
✅ Easily publishable configs, views, assets, etc.

---

## ⚙️ Requirements

* PHP	^8.2 | ^8.3 | ^8.4
* Laravel	^10.0 | ^11.0 | ^12.0
* firebase/php-jwt	*
* esanj/auth-bridge	*

🛑 Make sure all dependencies are installed via Composer.

---

## ⚙️ Installation & Setup

### 📥 Step 1: Install the package

```bash
composer require esanj/managers
```

### 📥 Step 2: Run the installer
```bash
php artisan manager:install
```
---

## 🛠️ Environment Configuration

Update your .env file with the following parameters:

```env
ACCOUNTING_BRIDGE_CLIENT_ID=your-client-id-from-accounting
ACCOUNTING_BRIDGE_CLIENT_SECRET=your-client-secret-from-accounting
ACCOUNTING_BRIDGE_BASE_URL=https://accounting-service.test
ACCOUNTING_BRIDGE_SUCCESS_REDIRECT=https://your-app.test/oauth/success

MANAGER_SUCCESS_REDIRECT=/admin/dashboard
MANAGER_ACCESS_DENIED_REDIRECT=/no-permission

MANAGER_PANEL_ROUTE_PREFIX=admin
MANAGER_API_ROUTE_PREFIX=api/admin
```

### 🔑 Explanation

Key	Description

```ACCOUNTING_BRIDGE_CLIENT_ID```	Client ID from the accounting service

```ACCOUNTING_BRIDGE_CLIENT_SECRET```	Client Secret from the accounting service

```ACCOUNTING_BRIDGE_BASE_URL```	Microservice base URL

```ACCOUNTING_BRIDGE_SUCCESS_REDIRECT```	Redirect after successful OAuth login

```MANAGER_SUCCESS_REDIRECT```	Redirect after successful token validation

```MANAGER_ACCESS_DENIED_REDIRECT```	Redirect when manager has no permission

```MANAGER_PANEL_ROUTE_PREFIX```	Web route group prefix (e.g., /admin)

```MANAGER_API_ROUTE_PREFIX```	API route group prefix (e.g., /api/admin)

---

## 📦 Publishable Files
You can customize the package via these publish commands:

### 🔧 What Command
Config file:	```php artisan vendor:publish --tag=esanj-manager-config```

Blade views:	```php artisan vendor:publish --tag=esanj-manager-views```

Language files:	```php artisan vendor:publish --tag=esanj-manager-lang```

Database migrations:	```php artisan vendor:publish --tag=esanj-manager-migrations```

Front-end assets (JS/CSS):	```php artisan vendor:publish --tag=esanj-manager-assets```

---

## 🔐 Middlewares
Middleware Purpose

```CheckAuthManagerMiddleware```	Ensures a manager is authenticated

```CheckManagerPermissionMiddleware```	Validates manager’s permission on routes

Use these to protect your web routes and API endpoints.

---

## 🧑‍💻 Artisan Commands

### ➕ Create a New Manager

```bash
php artisan manager:create
```

This command creates a manager user with the Admin role, which includes all permissions by default.

---

### 🚪 Route Access
To access the Manager Panel UI:

`route('managers.index')`

This route is available after the package is installed.

---
