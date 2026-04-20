<?php

namespace VormiaPHP\VormiaInertia\Contracts;

use VormiaPHP\VormiaInertia\Support\NpmCommandResult;

interface NpmPackageManager
{
    public function isAvailable(): bool;

    /**
     * @param  array<int, string>  $packages
     */
    public function install(array $packages): NpmCommandResult;

    /**
     * @param  array<int, string>  $packages
     */
    public function uninstall(array $packages): NpmCommandResult;

    /**
     * @param  array<int, string>  $packages
     */
    public function formatInstallCommand(array $packages): string;

    /**
     * @param  array<int, string>  $packages
     */
    public function formatUninstallCommand(array $packages): string;
}
