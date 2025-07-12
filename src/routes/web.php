<?php

use Esanj\Manager\Controllers\TokenController;
use Illuminate\Support\Facades\Route;


Route::middleware(config('manager.routes.middleware'))
    ->prefix(config('manager.routes.prefix'))
    ->name('manager.')
    ->group(function () {
        Route::get('/token', [TokenController::class, 'index'])->name('index');
        Route::post('/token', [TokenController::class, 'login'])->name('login');
    });
