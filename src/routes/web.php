<?php

use Esanj\Manager\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;


Route::middleware(['web'])
    ->prefix(config('manager.prefix', 'oauth'))
    ->name('oauth.')
    ->group(function () {
        Route::get('/', [OAuthController::class, 'redirect'])->name('redirect');
        Route::get('/callback', [OAuthController::class, 'callback'])->name('callback');
    });
