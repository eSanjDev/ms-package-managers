<?php

use Esanj\Manager\Http\Controllers\ManagerApiController;
use Esanj\Manager\Http\Controllers\ManagerAuthController;
use Esanj\Manager\Http\Controllers\ManagerController;
use Esanj\Manager\Http\Middleware\EnsureRequestIsNotRateLimitedMiddleware;
use Illuminate\Support\Facades\Route;


Route::middleware('web')->prefix(config('esanj.manager.routes.auth_prefix') . '/managers')->name('managers.auth.')->group(function () {
    Route::get('/token', [ManagerAuthController::class, 'index'])->name('index');
    Route::post('/token', [ManagerAuthController::class, 'login'])->middleware([EnsureRequestIsNotRateLimitedMiddleware::class])->name('login');
    Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('logout');
});

Route::prefix(config('esanj.manager.routes.panel_prefix'))->middleware(config('esanj.manager.middlewares.web'))->group(function () {
    Route::resource("/managers", ManagerController::class)->except(['show', 'destroy']);
    Route::get('/managers/{manager}/activities', [ManagerController::class, 'activities'])->name('activities');
});


Route::prefix(config('esanj.manager.routes.panel_prefix') . "/api")->middleware(config('esanj.manager.middlewares.web'))->group(function () {
    Route::apiResource("/managers", ManagerApiController::class)->only(['index', 'destroy']);
    Route::get("/managers/{manager}/activities", [ManagerApiController::class, 'activities']);
    Route::get("/managers/{manager}/activities/{activity}", [ManagerApiController::class, 'getLog']);

    Route::post('/managers/{manager}/restore', [ManagerApiController::class, 'restore']);
    Route::get('/managers/regenerate', [ManagerApiController::class, 'regenerate']);
});
