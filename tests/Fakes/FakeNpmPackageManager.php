<?php

namespace VormiaPHP\VormiaInertia\Tests\Fakes;

use VormiaPHP\VormiaInertia\Contracts\NpmPackageManager;
use VormiaPHP\VormiaInertia\Support\NpmCommandResult;

class FakeNpmPackageManager implements NpmPackageManager
{
    public bool $available = true;

    /**
     * @var array<int, array<int, string>>
     */
    public array $installed = [];

    /**
     * @var array<int, array<int, string>>
     */
    public array $uninstalled = [];

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function install(array $packages): NpmCommandResult
    {
        $this->installed[] = $packages;

        return new NpmCommandResult(true, 'installed');
    }

    public function uninstall(array $packages): NpmCommandResult
    {
        $this->uninstalled[] = $packages;

        return new NpmCommandResult(true, 'uninstalled');
    }

    public function formatInstallCommand(array $packages): string
    {
        return 'npm install ' . implode(' ', $packages);
    }

    public function formatUninstallCommand(array $packages): string
    {
        return 'npm uninstall ' . implode(' ', $packages);
    }
}
