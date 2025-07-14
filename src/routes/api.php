<?php

use Esanj\Manager\Http\Controllers\ManagerApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->prefix(config('manager.routes.api_prefix'))->name('api.managers.')->group(function () {
    Route::get('/', [ManagerApiController::class, 'index'])->name('index');
    Route::delete('/{manager}', [ManagerApiController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore', [ManagerApiController::class, 'restore'])->name('restore');
    Route::get('/regenerate', [ManagerApiController::class, 'regenerate'])->name('regenerate');
});
