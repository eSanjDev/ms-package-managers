<?php

namespace Esanj\Manager\Providers;

use Esanj\Manager\Livewire\AuthPassword;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Repositories\ManagerRepository;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/manager.php', 'manager');

        $this->app->bind(
            ManagerRepository::class,
            fn($app) => new ManagerRepository(new Manager())
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/manager.php' => config_path('manager.php'),
        ], 'manager-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/manager'),
        ], 'manager-views');

        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/manager'),
        ], 'manager-lang');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'manager');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'manager');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Livewire::component('manager.auth-password', AuthPassword::class);

        $this->publishes([
            __DIR__ . '/../public' => public_path(),
        ], 'manager-assets');
    }
}
