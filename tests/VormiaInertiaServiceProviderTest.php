<?php

namespace VormiaPHP\VormiaInertia\Tests;

use Illuminate\Support\Facades\Artisan;
use VormiaPHP\VormiaInertia\VormiaInertia;

class VormiaInertiaServiceProviderTest extends TestCase
{
    public function test_config_is_registered(): void
    {
        $this->assertIsArray(config('vormia-inertia'));
    }

    public function test_vormia_inertia_binding_is_registered(): void
    {
        $this->assertInstanceOf(VormiaInertia::class, $this->app->make('vormia.inertia'));
    }

    public function test_install_and_uninstall_commands_are_registered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('vormia-inertia:install', $commands);
        $this->assertArrayHasKey('vormia-inertia:uninstall', $commands);
    }
}
