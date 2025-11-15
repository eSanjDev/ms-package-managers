<?php

namespace Esanj\Manager\Providers;

use Esanj\Manager\Commands\CreateManagerCommand;
use Esanj\Manager\Commands\ImportPermissionsCommand;
use Esanj\Manager\Commands\InstallCommand;
use Esanj\Manager\Http\Middleware\CheckAuthManagerMiddleware;
use Esanj\Manager\Http\Middleware\CheckManagerPermissionMiddleware;
use Esanj\Manager\Models\Manager;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
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
        $this->registerMiddleware();
        $this->registerPermissions();

    }

    private function registerPermissions(): void
    {
        $permissions = config('esanj.manager.permissions');

        foreach ($permissions as $key => $permission) {
            Gate::define($key, function (Manager $manager) use ($key) {
                return $manager->hasPermission($key);
            });
        }
    }

    private function registerMiddleware(): void
    {
        $router = app(Router::class);

        $router->aliasMiddleware('manager.auth', CheckAuthManagerMiddleware::class);
        $router->aliasMiddleware('manager.permission', CheckManagerPermissionMiddleware::class);
    }

    /**
     * Register configuration files.
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/manager.php'), 'esanj.manager');

        config([
            'auth.providers.managers' => [
                'driver' => 'eloquent',
                'model' => Manager::class,
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
        if (!config('esanj.manager.just_api')) {
            $this->loadRoutesFrom($this->packagePath('routes/web.php'));
        }

        $this->loadRoutesFrom($this->packagePath('routes/api.php'));
    }

    /**
     * Register views.
     */
    private function registerViews(): void
    {
        if (!config('esanj.manager.just_api')) {
            $this->loadViewsFrom($this->packagePath('views'), 'manager');
        }
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
                $this->packagePath('assets') => resource_path('/assets/packages/manager'),
            ], 'esanj-manager-assets');

            $this->publishes([
                $this->packagePath('config/manager.php') => config_path('esanj/manager.php'),
            ], 'esanj-manager-config');

            $this->publishes([
                $this->packagePath('views') => resource_path('views/vendor/manager'),
            ], 'esanj-manager-views');

            $this->publishes([
                $this->packagePath('lang') => lang_path('vendor/manager'),
            ], 'esanj-manager-lang');

            $this->publishes([
                $this->packagePath('database/migrations/') => database_path('migrations'),
            ], 'esanj-manager-migrations');
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
