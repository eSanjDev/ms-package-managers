<?php

use Esanj\Manager\Http\Controllers\ManagerController;
use Esanj\Manager\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;


Route::middleware(config('manager.middlewares.web'))
    ->prefix(config('manager.routes.auth_prefix'))
    ->name('manager.auth.')
    ->group(function () {
        Route::get('/token', [TokenController::class, 'index'])->name('index');
        Route::post('/token', [TokenController::class, 'login'])->name('login');
    });

Route::resource(config('manager.routes.panel_prefix') . "/managers", ManagerController::class)
    ->middleware(config('manager.middlewares.web'))
    ->except(['show', 'destroy']);
