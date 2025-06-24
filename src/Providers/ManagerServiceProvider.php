<?php

namespace Esanj\Manager\Providers;

use Esanj\Manager\Commands\InstallCommand;
use Esanj\Manager\Livewire\AuthPassword;
use Esanj\Manager\Repositories\ManagerRepository;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ManagerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerCommands();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerLivewireComponents();
        $this->registerPublishing();
    }

    /**
     * Register configuration files.
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/manager.php'), 'manager');
    }

    /**
     * Register console commands.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Register routes.
     */
    private function registerRoutes(): void
    {
        $this->loadRoutesFrom($this->packagePath('routes/web.php'));
    }

    /**
     * Register views.
     */
    private function registerViews(): void
    {
        $this->loadViewsFrom($this->packagePath('resources/views'), 'manager');
    }

    /**
     * Register translations.
     */
    private function registerTranslations(): void
    {
        $this->loadTranslationsFrom($this->packagePath('lang'), 'manager');
    }

    /**
     * Register migrations.
     */
    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
    }

    /**
     * Register Livewire components.
     */
    private function registerLivewireComponents(): void
    {
        Livewire::component('manager.auth-password', AuthPassword::class);
    }

    /**
     * Register assets, configs, views, langs, migrations for publishing.
     */
    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->packagePath('public/assets') => public_path('assets/vendor/manager'),
            ], 'manager-assets');

            $this->publishes([
                $this->packagePath('config/manager.php') => config_path('manager.php'),
            ], 'manager-config');

            $this->publishes([
                $this->packagePath('resources/views') => resource_path('views/vendor/manager'),
            ], 'manager-views');

            $this->publishes([
                $this->packagePath('lang') => lang_path('vendor/manager'),
            ], 'manager-lang');

            $this->publishes([
                $this->packagePath('database/migrations/') => database_path('migrations'),
            ], 'manager-migrations');
        }
    }

    /**
     * Get full path to a package sub-folder/file.
     */
    private function packagePath(string $path): string
    {
        return dirname(__DIR__) . '/' . ltrim($path, '/');
    }
}
