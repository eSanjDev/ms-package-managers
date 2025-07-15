<?php

namespace Esanj\Manager\Providers;

use Esanj\Manager\Commands\CreateManagerCommand;
use Esanj\Manager\Commands\ImportPermissionsCommand;
use Esanj\Manager\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

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
        $this->registerPublishing();
    }

    /**
     * Register configuration files.
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/manager.php'), 'manager');

        config([
            'auth.providers.managers' => [
                'driver' => 'eloquent',
                'model' => \Esanj\Manager\Models\Manager::class,
            ],
            'auth.guards.manager' => [
                'driver' => 'session',
                'provider' => 'managers',
            ],
        ]);
    }

    /**
     * Register console commands.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                CreateManagerCommand::class,
                ImportPermissionsCommand::class,
            ]);
        }
    }

    /**
     * Register routes.
     */
    private function registerRoutes(): void
    {
        $this->loadRoutesFrom($this->packagePath('routes/web.php'));
        $this->loadRoutesFrom($this->packagePath('routes/api.php'));
    }

    /**
     * Register views.
     */
    private function registerViews(): void
    {
        $this->loadViewsFrom($this->packagePath('views'), 'manager');
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
     * Register assets, configs, views, langs, migrations for publishing.
     */
    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->packagePath('assets') => public_path('assets/vendor/manager'),
            ], 'manager-assets');

            $this->publishes([
                $this->packagePath('config/manager.php') => config_path('manager.php'),
            ], 'manager-config');

            $this->publishes([
                $this->packagePath('views') => resource_path('views/vendor/manager'),
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
