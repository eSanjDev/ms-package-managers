<?php

use Esanj\Manager\Http\Controllers\ManagerApiController;
use Esanj\Manager\Http\Controllers\ManagerAuthApiController;
use Esanj\Manager\Http\Middleware\EnsureRequestIsNotRateLimitedMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix(config('esanj.manager.routes.api_prefix') . '/managers')->middleware(['api'])->group(function () {
    Route::get('/redirect', [ManagerAuthApiController::class, 'redirectToAuthBridge']);
    Route::get('/verify', [ManagerAuthApiController::class, 'verifyManagerCode']);
    Route::post('/authenticate', [ManagerAuthApiController::class, 'authenticateViaBridge'])->middleware([EnsureRequestIsNotRateLimitedMiddleware::class]);
});


Route::prefix(config('esanj.manager.routes.api_prefix'))
    ->middleware(array_merge(['api'], config('esanj.manager.middlewares.api')))
    ->group(function () {
        Route::apiResource("/managers", ManagerApiController::class)->names("api.managers");

        Route::post('/managers/{manager}/restore', [ManagerApiController::class, 'restore']);

        Route::get('/managers/{manager}/meta/{key}', [ManagerApiController::class, 'getMeta']);
        Route::post('/managers/{manager}/meta', [ManagerApiController::class, 'setMeta']);

        Route::get("/managers/{manager}/activities", [ManagerApiController::class, 'activities']);
        Route::get("/managers/{manager}/activities/{activity}", [ManagerApiController::class, 'getLog']);
    });
