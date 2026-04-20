<?php

namespace VormiaPHP\VormiaInertia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use VormiaPHP\VormiaInertia\Contracts\NpmPackageManager;
use VormiaPHP\VormiaInertia\Support\ApplicationPaths;
use VormiaPHP\VormiaInertia\Support\InstallManifestRepository;
use VormiaPHP\VormiaInertia\Support\MarkerPatcher;
use VormiaPHP\VormiaInertia\Support\StackDefinition;
use VormiaPHP\VormiaInertia\Support\StubRepository;

class UninstallCommand extends Command
{
    protected $signature = 'vormia-inertia:uninstall
        {--force : Run without confirmation prompts}';

    protected $description = 'Remove Vormia Inertia-managed host-app changes conservatively';

    public function handle(
        ApplicationPaths $paths,
        StubRepository $stubs,
        InstallManifestRepository $manifestRepository,
        NpmPackageManager $npm,
    ): int {
        $this->components->warn('This will remove Vormia Inertia-managed bridge files, markers, and npm packages when it is safe to do so.');

        if (! $this->option('force') && ! $this->confirm('Continue with vormia-inertia:uninstall?', false)) {
            $this->components->warn('Uninstall aborted.');

            return self::SUCCESS;
        }

        $manifest = $manifestRepository->read();

        $this->cleanupBootstrapApp($paths);
        $this->cleanupMiddleware($paths, $stubs, $manifest);
        $this->cleanupManagedWholeFiles($paths, $stubs, $manifest);
        $this->cleanupNpmPackages($paths, $npm, $manifest);

        $manifestRepository->delete();

        $this->components->info('Vormia Inertia uninstall completed.');

        return self::SUCCESS;
    }

    private function cleanupBootstrapApp(ApplicationPaths $paths): void
    {
        $path = $paths->bootstrap('app.php');

        if (! File::exists($path)) {
            return;
        }

        $contents = File::get($path);
        $updated = MarkerPatcher::removeBlock($contents, 'bootstrap-middleware');

        if ($updated !== $contents) {
            File::put($path, $updated);
            $this->line("  <info>cleaned</info> {$path}");
        }
    }

    /**
     * @param  array<string, mixed>|null  $manifest
     */
    private function cleanupMiddleware(ApplicationPaths $paths, StubRepository $stubs, ?array $manifest): void
    {
        $path = $paths->app('Http/Middleware/HandleInertiaRequests.php');

        if (! File::exists($path)) {
            return;
        }

        $mode = $manifest['files']['app/Http/Middleware/HandleInertiaRequests.php']['mode'] ?? null;
        $contents = File::get($path);
        $stub = $stubs->handleInertiaRequests();

        if ($mode === 'created' && $contents === $stub) {
            File::delete($path);
            $this->line("  <info>removed</info> {$path}");

            return;
        }

        if (MarkerPatcher::hasBlock($contents, 'shared-props')) {
            $updated = MarkerPatcher::removeBlock($contents, 'shared-props');
            File::put($path, $updated);
            $this->line("  <info>cleaned</info> {$path}");

            return;
        }

        if ($mode === 'created') {
            $this->line("  <comment>kept</comment> {$path} (file was modified after install)");
        }
    }

    /**
     * @param  array<string, mixed>|null  $manifest
     */
    private function cleanupManagedWholeFiles(ApplicationPaths $paths, StubRepository $stubs, ?array $manifest): void
    {
        if ($manifest === null) {
            return;
        }

        $stack = $manifest['stack'] ?? null;
        $lang = $manifest['lang'] ?? null;

        if (! is_string($stack) || ! is_string($lang)) {
            return;
        }

        StackDefinition::assertValid($stack, $lang);

        $expectedFiles = [
            'config/vormia-inertia.php' => $stubs->config(),
            'resources/views/app.blade.php' => $stubs->rootView($stack, $lang),
            'resources/css/app.css' => $stubs->appCss(),
            StackDefinition::entryFile($stack, $lang) => $stubs->entry($stack, $lang),
        ];

        foreach ($expectedFiles as $relativePath => $stubContents) {
            $mode = $manifest['files'][$relativePath]['mode'] ?? null;

            if ($mode !== 'created') {
                continue;
            }

            $path = $paths->base($relativePath);

            if (! File::exists($path)) {
                continue;
            }

            $current = File::get($path);

            if ($current === $stubContents) {
                File::delete($path);
                $this->line("  <info>removed</info> {$path}");
            } else {
                $this->line("  <comment>kept</comment> {$path} (file was modified after install)");
            }
        }
    }

    /**
     * @param  array<string, mixed>|null  $manifest
     */
    private function cleanupNpmPackages(ApplicationPaths $paths, NpmPackageManager $npm, ?array $manifest): void
    {
        $packages = $manifest['npm_packages'] ?? [];

        if (! is_array($packages) || $packages === []) {
            return;
        }

        if (! File::exists($paths->base('package.json'))) {
            return;
        }

        if (! $npm->isAvailable()) {
            $this->line('  Run this manually to remove npm packages: ' . $npm->formatUninstallCommand($packages));

            return;
        }

        $result = $npm->uninstall($packages);

        if ($result->successful) {
            $this->line('  <info>removed</info> npm packages');
        } else {
            $this->line('  <comment>kept</comment> npm packages (npm uninstall failed)');
        }
    }
}
