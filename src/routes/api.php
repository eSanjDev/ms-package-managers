<?php

use Esanj\Manager\Http\Controllers\ManagerApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('manager.middlewares.api'))->prefix(config('manager.routes.api_prefix'))
    ->name('api.managers.')->group(function () {
        Route::apiResource("/managers", ManagerApiController::class);
        Route::post('/managers/{manager}/restore', [ManagerApiController::class, 'restore'])->name('restore');
        Route::get('/managers/regenerate', [ManagerApiController::class, 'regenerate'])->name('regenerate');
    });
