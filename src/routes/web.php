<?php

use Esanj\Manager\Http\Controllers\ManagerAuthController;
use Esanj\Manager\Http\Controllers\ManagerController;
use Esanj\Manager\Http\Middleware\EnsureRequestIsNotRateLimitedMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix(config('esanj.manager.routes.auth_prefix') . '/managers')->middleware('web')->name('managers.auth.')->group(function () {
    Route::get('/token', [ManagerAuthController::class, 'index'])->name('index');
    Route::post('/token', [ManagerAuthController::class, 'login'])->middleware([EnsureRequestIsNotRateLimitedMiddleware::class])->name('login');
    Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('logout');
});


Route::prefix(config('esanj.manager.routes.panel_prefix'))
    ->middleware(config('esanj.manager.middlewares.web'))
    ->group(function () {
        Route::resource("/managers", ManagerController::class)->except(['show']);
        Route::post('/managers/{manager}/restore', [ManagerController::class, 'restore'])->name('managers.restore');

        Route::get('/managers/{manager}/activities', [ManagerController::class, 'activities'])->name('managers.activities');
        Route::get("/managers/{manager}/activities/{activity}", [ManagerController::class, 'getLog'])->name('managers.activities.log');
    });
