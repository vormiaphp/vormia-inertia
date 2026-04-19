<?php

namespace VormiaPHP\VormiaInertia\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use VormiaPHP\VormiaInertia\VormiaInertiaServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            VormiaInertiaServiceProvider::class,
        ];
    }
}
