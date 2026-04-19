<?php

namespace VormiaPHP\VormiaInertia\Tests;

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
}
