<?php

namespace VormiaPHP\VormiaInertia\Support;

use Illuminate\Support\Facades\Process;
use VormiaPHP\VormiaInertia\Contracts\NpmPackageManager;

class ProcessNpmPackageManager implements NpmPackageManager
{
    public function __construct(
        private readonly ApplicationPaths $paths,
    ) {
    }

    public function isAvailable(): bool
    {
        return Process::run('npm --version')->successful();
    }

    public function install(array $packages): NpmCommandResult
    {
        return $this->run($this->formatInstallCommand($packages));
    }

    public function uninstall(array $packages): NpmCommandResult
    {
        return $this->run($this->formatUninstallCommand($packages));
    }

    public function formatInstallCommand(array $packages): string
    {
        return 'npm install ' . implode(' ', $packages);
    }

    public function formatUninstallCommand(array $packages): string
    {
        return 'npm uninstall ' . implode(' ', $packages);
    }

    private function run(string $command): NpmCommandResult
    {
        $result = Process::path($this->paths->base())->run($command);

        return new NpmCommandResult(
            $result->successful(),
            $result->output(),
            $result->errorOutput(),
        );
    }
}
