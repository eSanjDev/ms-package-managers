<?php

namespace Esanj\Manager\Providers;

use Esanj\Manager\Livewire\AuthPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ManagerServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->mergeConfigFrom(__DIR__ . '/../config/manager.php', 'manager');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'manager');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'manager');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');


        Livewire::component('manager.auth-password', AuthPassword::class);

    }
}
