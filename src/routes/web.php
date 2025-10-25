<?php

use Esanj\Manager\Http\Controllers\ManagerApiController;
use Esanj\Manager\Http\Controllers\ManagerAuthController;
use Esanj\Manager\Http\Controllers\ManagerController;
use Esanj\Manager\Http\Middleware\EnsureRequestIsNotRateLimitedMiddleware;
use Illuminate\Support\Facades\Route;


Route::middleware('web')
    ->prefix(config('esanj.manager.routes.auth_prefix') . '/managers')
    ->name('managers.auth.')
    ->group(function () {
        Route::get('/token', [ManagerAuthController::class, 'index'])->name('index');
        Route::post('/token', [ManagerAuthController::class, 'login'])->middleware([EnsureRequestIsNotRateLimitedMiddleware::class])->name('login');
        Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('logout');
    });

Route::resource(config('esanj.manager.routes.panel_prefix') . "/managers", ManagerController::class)
    ->middleware(config('esanj.manager.middlewares.web'))
    ->except(['show', 'destroy']);


Route::prefix(config('esanj.manager.routes.panel_prefix') . "/api")->middleware(config('esanj.manager.middlewares.web'))
    ->group(function () {
        Route::apiResource("/managers", ManagerApiController::class)->only(['index', 'destroy']);

        Route::post('/managers/{manager}/restore', [ManagerApiController::class, 'restore']);
        Route::get('/managers/regenerate', [ManagerApiController::class, 'regenerate']);
    });
