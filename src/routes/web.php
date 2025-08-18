<?php

use Esanj\Manager\Http\Controllers\ManagerController;
use Esanj\Manager\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

if (!config('esanj.manager.just_api')) {
    Route::middleware(config('esanj.manager.middlewares.web'))
        ->prefix(config('esanj.manager.routes.auth_prefix'))
        ->name('manager.auth.')
        ->group(function () {
            Route::get('/token', [TokenController::class, 'index'])->name('index');
            Route::post('/token', [TokenController::class, 'login'])->name('login');
        });

    Route::resource(config('esanj.manager.routes.panel_prefix') . "/managers", ManagerController::class)
        ->middleware(config('esanj.manager.middlewares.web'))
        ->except(['show', 'destroy']);
}
