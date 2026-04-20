<?php

namespace VormiaPHP\VormiaInertia;

use Illuminate\Support\ServiceProvider;
use VormiaPHP\VormiaInertia\Console\Commands\InstallCommand;
use VormiaPHP\VormiaInertia\Console\Commands\UninstallCommand;
use VormiaPHP\VormiaInertia\Contracts\NpmPackageManager;
use VormiaPHP\VormiaInertia\Support\ApplicationPaths;
use VormiaPHP\VormiaInertia\Support\InstallManifestRepository;
use VormiaPHP\VormiaInertia\Support\ProcessNpmPackageManager;
use VormiaPHP\VormiaInertia\Support\StubRepository;

class VormiaInertiaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/vormia-inertia.php', 'vormia-inertia');

        $this->app->singleton('vormia.inertia', function () {
            return new VormiaInertia();
        });

        $this->app->singleton(ApplicationPaths::class, fn () => new ApplicationPaths(base_path()));
        $this->app->singleton(StubRepository::class, fn () => new StubRepository(__DIR__ . '/stubs'));
        $this->app->singleton(InstallManifestRepository::class, fn ($app) => new InstallManifestRepository($app->make(ApplicationPaths::class)));
        $this->app->bind(NpmPackageManager::class, fn ($app) => new ProcessNpmPackageManager($app->make(ApplicationPaths::class)));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                UninstallCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/config/vormia-inertia.php' => config_path('vormia-inertia.php'),
            ], 'vormia-inertia-config');
        }
    }
}
