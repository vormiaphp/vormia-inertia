<?php

namespace VormiaPHP\VormiaInertia;

use Illuminate\Support\ServiceProvider;

class VormiaInertiaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/vormia-inertia.php', 'vormia-inertia');

        $this->app->singleton('vormia.inertia', function () {
            return new VormiaInertia();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/vormia-inertia.php' => config_path('vormia-inertia.php'),
            ], 'vormia-inertia-config');
        }
    }
}
