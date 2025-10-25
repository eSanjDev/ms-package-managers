<?php

use Esanj\Manager\Http\Controllers\ManagerApiController;
use Esanj\Manager\Http\Controllers\ManagerAuthApiController;
use Esanj\Manager\Http\Middleware\EnsureRequestIsNotRateLimitedMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix(config('esanj.manager.routes.api_prefix') . '/managers')->name('api.managers.')->group(function () {
    Route::get('/redirect', [ManagerAuthApiController::class, 'redirectToAuthBridge']);
    Route::get('/verify', [ManagerAuthApiController::class, 'verifyManagerCode']);
    Route::post('/authenticate', [ManagerAuthApiController::class, 'authenticateViaBridge'])
        ->middleware([EnsureRequestIsNotRateLimitedMiddleware::class]);
});


Route::middleware(config('esanj.manager.middlewares.api'))
    ->prefix(config('esanj.manager.routes.api_prefix'))
    ->name('api.managers.')
    ->group(function () {
        Route::post('/managers/{manager}/restore', [ManagerApiController::class, 'restore']);
        Route::get('/managers/regenerate', [ManagerApiController::class, 'regenerate']);

        Route::apiResource("/managers", ManagerApiController::class);

        Route::get('/managers/{manager}/meta/{key}', [ManagerApiController::class, 'getMeta']);
        Route::post('/managers/{manager}/meta', [ManagerApiController::class, 'setMeta']);
    });
