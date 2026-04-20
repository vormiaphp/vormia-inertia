<?php

namespace VormiaPHP\VormiaInertia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use VormiaPHP\VormiaInertia\Contracts\NpmPackageManager;
use VormiaPHP\VormiaInertia\Support\ApplicationPaths;
use VormiaPHP\VormiaInertia\Support\InstallManifestRepository;
use VormiaPHP\VormiaInertia\Support\MarkerPatcher;
use VormiaPHP\VormiaInertia\Support\StackDefinition;
use VormiaPHP\VormiaInertia\Support\StubRepository;

class InstallCommand extends Command
{
    protected $signature = 'vormia-inertia:install
        {--stack= : The Inertia client stack to scaffold (react, vue, or svelte)}
        {--lang= : The entry language to scaffold (js or ts)}
        {--replace=* : Replace managed entry assets such as app.js, app.tsx, app.ts, app.jsx, or app.css}
        {--force : Overwrite safe published files without extra confirmation}';

    protected $description = 'Install Vormia Inertia scaffolding, shared props, and host-app bridge files';

    public function handle(
        ApplicationPaths $paths,
        StubRepository $stubs,
        InstallManifestRepository $manifestRepository,
        NpmPackageManager $npm,
    ): int {
        try {
            $this->components->info('Installing Vormia Inertia...');

            $this->assertVormiaInstalled();

            [$stack, $lang] = $this->resolveStackAndLanguage();
            $replaceTargets = $this->normalizeReplaceTargets($paths, (array) $this->option('replace'), $stack, $lang);

            $entryPath = $paths->base(StackDefinition::entryFile($stack, $lang));
            $cssPath = $paths->resource('css/app.css');

            $replaceTargets = $this->confirmInteractiveReplacement($replaceTargets, $entryPath, "Replace existing {$paths->relative($entryPath)} with the {$stack}/{$lang} Vormia Inertia entry file?");
            $replaceTargets = $this->confirmInteractiveReplacement($replaceTargets, $cssPath, "Replace existing {$paths->relative($cssPath)} with the Vormia Inertia CSS stub?");

            $manifest = [
                'stack' => $stack,
                'lang' => $lang,
                'entry_file' => StackDefinition::entryFile($stack, $lang),
                'npm_packages' => StackDefinition::installPackages($stack, $lang),
                'files' => [],
                'installed_at' => Carbon::now()->toIso8601String(),
            ];

            $manifest['files']['config/vormia-inertia.php'] = [
                'mode' => $this->writeManagedFile(
                    $paths->config('vormia-inertia.php'),
                    $stubs->config(),
                    (bool) $this->option('force'),
                ),
            ];

            $manifest['files']['app/Http/Middleware/HandleInertiaRequests.php'] = [
                'mode' => $this->installHandleInertiaRequests($paths, $stubs),
            ];

            $manifest['files']['bootstrap/app.php'] = [
                'mode' => $this->patchBootstrapApp($paths),
            ];

            $manifest['files']['resources/views/app.blade.php'] = [
                'mode' => $this->writeManagedFile(
                    $paths->resource('views/app.blade.php'),
                    $stubs->rootView($stack, $lang),
                    false,
                ),
            ];

            $manifest['files'][StackDefinition::entryFile($stack, $lang)] = [
                'mode' => $this->writeManagedFile(
                    $entryPath,
                    $stubs->entry($stack, $lang),
                    in_array($entryPath, $replaceTargets, true),
                ),
            ];

            $manifest['files']['resources/css/app.css'] = [
                'mode' => $this->writeManagedFile(
                    $cssPath,
                    $stubs->appCss(),
                    in_array($cssPath, $replaceTargets, true),
                ),
            ];

            $manifest['npm'] = $this->installNpmPackages($paths, $npm, $stack, $lang);

            $manifestRepository->write($manifest);

            $this->components->info('Vormia Inertia has been installed.');
            $this->printNextSteps($stack, $lang, $manifest['npm']['packages']);

            return self::SUCCESS;
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function assertVormiaInstalled(): void
    {
        if (! class_exists(\VormiaPHP\Vormia\VormiaServiceProvider::class)) {
            throw new InvalidArgumentException('vormiaphp/vormia must be installed before running vormia-inertia:install.');
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveStackAndLanguage(): array
    {
        $stack = $this->option('stack');
        $lang = $this->option('lang');

        if ($stack === null) {
            if ($this->input->isInteractive()) {
                $stack = $this->choice('Which Inertia client stack would you like to install?', StackDefinition::stacks(), 0);
            } else {
                throw new InvalidArgumentException('The --stack option is required when running without interaction.');
            }
        }

        if ($lang === null) {
            if ($this->input->isInteractive()) {
                $lang = $this->choice('Which entry language would you like to scaffold?', StackDefinition::languages(), 1);
            } else {
                throw new InvalidArgumentException('The --lang option is required when running without interaction.');
            }
        }

        StackDefinition::assertValid($stack, $lang);

        return [$stack, $lang];
    }

    /**
     * @param  array<int, string>  $replaceOptions
     * @return array<int, string>
     */
    private function normalizeReplaceTargets(ApplicationPaths $paths, array $replaceOptions, string $stack, string $lang): array
    {
        $accepted = StackDefinition::acceptedReplaceTargets($stack, $lang);
        $entryPath = $paths->base(StackDefinition::entryFile($stack, $lang));
        $targets = [];

        foreach ($replaceOptions as $target) {
            if (! in_array($target, $accepted, true)) {
                throw new InvalidArgumentException("The --replace target [{$target}] is not supported for the {$stack}/{$lang} install.");
            }

            $targets[] = match ($target) {
                'app.css' => $paths->resource('css/app.css'),
                default => $entryPath,
            };
        }

        return array_values(array_unique($targets));
    }

    /**
     * @param  array<int, string>  $replaceOptions
     * @return array<int, string>
     */
    private function confirmInteractiveReplacement(array $replaceTargets, string $path, string $question): array
    {
        if (! File::exists($path)
            || in_array($path, $replaceTargets, true)
            || ! $this->input->isInteractive()
            || (bool) $this->option('force')) {
            return $replaceTargets;
        }

        if ($this->confirm($question, false)) {
            $replaceTargets[] = $path;
        }

        return array_values(array_unique($replaceTargets));
    }

    private function writeManagedFile(string $path, string $contents, bool $replaceExisting): string
    {
        File::ensureDirectoryExists(dirname($path));

        if (! File::exists($path)) {
            File::put($path, $contents);

            $this->line("  <info>created</info> {$path}");

            return 'created';
        }

        $current = File::get($path);

        if ($current === $contents) {
            $this->line("  <comment>unchanged</comment> {$path}");

            return 'unchanged';
        }

        if (! $replaceExisting && ! str_contains($current, MarkerPatcher::MANAGED_FILE_MARKER)) {
            $this->line("  <comment>skipped</comment> {$path}");

            return 'skipped';
        }

        File::put($path, $contents);

        $mode = $replaceExisting && ! str_contains($current, MarkerPatcher::MANAGED_FILE_MARKER)
            ? 'replaced'
            : 'updated';

        $this->line("  <info>{$mode}</info> {$path}");

        return $mode;
    }

    private function installHandleInertiaRequests(ApplicationPaths $paths, StubRepository $stubs): string
    {
        $path = $paths->app('Http/Middleware/HandleInertiaRequests.php');
        $stub = $stubs->handleInertiaRequests();

        if (! File::exists($path)) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $stub);
            $this->line("  <info>created</info> {$path}");

            return 'created';
        }

        $contents = File::get($path);

        if (MarkerPatcher::hasBlock($contents, 'shared-props')) {
            $managedBlock = $this->sharedPropsBlock();
            $contents = MarkerPatcher::removeBlock($contents, 'shared-props');
            $updated = $this->injectSharedProps($contents, $managedBlock);

            if ($updated === null) {
                $this->components->warn('Could not refresh shared props in HandleInertiaRequests.php. Please merge the new shared props manually.');

                return 'skipped';
            }

            File::put($path, $updated);
            $this->line("  <info>updated</info> {$path}");

            return 'updated';
        }

        $updated = $this->injectSharedProps($contents, $this->sharedPropsBlock());

        if ($updated === null) {
            $this->components->warn('Could not patch HandleInertiaRequests.php automatically. Please merge the shared props block manually.');

            return 'skipped';
        }

        File::put($path, $updated);
        $this->line("  <info>patched</info> {$path}");

        return 'patched';
    }

    private function patchBootstrapApp(ApplicationPaths $paths): string
    {
        $path = $paths->bootstrap('app.php');

        if (! File::exists($path)) {
            $this->components->warn('bootstrap/app.php was not found. Please add HandleInertiaRequests to your web middleware manually.');

            return 'skipped';
        }

        $contents = File::get($path);
        $managedBlock = $this->bootstrapMiddlewareBlock();

        if (MarkerPatcher::hasBlock($contents, 'bootstrap-middleware')) {
            $contents = MarkerPatcher::removeBlock($contents, 'bootstrap-middleware');
        } elseif (str_contains($contents, '\App\Http\Middleware\HandleInertiaRequests::class')
            || str_contains($contents, 'App\Http\Middleware\HandleInertiaRequests::class')) {
            $this->line("  <comment>unchanged</comment> {$path}");

            return 'unchanged';
        }

        $updated = null;

        if (preg_match('/(->withMiddleware\(function\s*\([^\)]*\)\s*:\s*void\s*\{\R)/', $contents) === 1) {
            $updated = preg_replace(
                '/(->withMiddleware\(function\s*\([^\)]*\)\s*:\s*void\s*\{\R)/',
                '$1' . $managedBlock . PHP_EOL,
                $contents,
                1,
            );
        } elseif (str_contains($contents, '->withExceptions(')) {
            $newMiddlewareChain = "    ->withMiddleware(function (\\Illuminate\\Foundation\\Configuration\\Middleware \$middleware): void {\n{$managedBlock}\n    })\n";
            $updated = preg_replace('/(\s*->withExceptions\()/m', $newMiddlewareChain . '$1', $contents, 1);
        }

        if ($updated === null || $updated === $contents) {
            $this->components->warn('Could not patch bootstrap/app.php automatically. Please append HandleInertiaRequests to the web middleware stack manually.');

            return 'skipped';
        }

        File::put($path, $updated);
        $this->line("  <info>patched</info> {$path}");

        return 'patched';
    }

    private function sharedPropsBlock(): string
    {
        return "            // vormia-inertia:start shared-props\n"
            . "            'notification' => fn () => \\Vormia\\Vormia\\Services\\NotificationService::current(),\n"
            . "            'auth' => [\n"
            . "                'user' => fn () => \$request->user()?->only(['id', 'name', 'email']),\n"
            . "            ],\n"
            . "            'vormia' => [\n"
            . "                'inertia' => [\n"
            . "                    'package' => 'vormiaphp/vormia-inertia',\n"
            . "                ],\n"
            . "            ],\n"
            . "            // vormia-inertia:end shared-props";
    }

    private function bootstrapMiddlewareBlock(): string
    {
        return "        // vormia-inertia:start bootstrap-middleware\n"
            . "        \$middleware->web(append: [\n"
            . "            \\App\\Http\\Middleware\\HandleInertiaRequests::class,\n"
            . "        ]);\n"
            . "        // vormia-inertia:end bootstrap-middleware";
    }

    private function injectSharedProps(string $contents, string $block): ?string
    {
        if (preg_match('/(\.\.\.parent::share\(\$request\),\R)/', $contents) === 1) {
            return preg_replace('/(\.\.\.parent::share\(\$request\),\R)/', '$1' . $block . PHP_EOL, $contents, 1);
        }

        if (preg_match('/(return\s+array_merge\(\s*parent::share\(\$request\),\s*\[\R)/', $contents) === 1) {
            return preg_replace('/(return\s+array_merge\(\s*parent::share\(\$request\),\s*\[\R)/', '$1' . $block . PHP_EOL, $contents, 1);
        }

        return null;
    }

    /**
     * @return array{status: string, packages: array<int, string>}
     */
    private function installNpmPackages(ApplicationPaths $paths, NpmPackageManager $npm, string $stack, string $lang): array
    {
        $packages = StackDefinition::installPackages($stack, $lang);

        if (! File::exists($paths->base('package.json'))) {
            $this->components->warn('package.json was not found. Skipping npm install.');
            $this->line('  Run this manually after you add a package.json: ' . $npm->formatInstallCommand($packages));

            return ['status' => 'skipped-no-package-json', 'packages' => $packages];
        }

        if (! $npm->isAvailable()) {
            $this->components->warn('npm is not available in this environment. Skipping npm install.');
            $this->line('  Run this manually: ' . $npm->formatInstallCommand($packages));

            return ['status' => 'skipped-no-npm', 'packages' => $packages];
        }

        $this->line('  Installing npm packages: ' . implode(', ', $packages));

        $result = $npm->install($packages);

        if (! $result->successful) {
            $this->components->warn('npm install did not complete successfully.');
            if ($result->errorOutput !== '') {
                $this->line('  ' . trim($result->errorOutput));
            }

            return ['status' => 'failed', 'packages' => $packages];
        }

        $this->line('  <info>installed</info> npm packages');

        return ['status' => 'installed', 'packages' => $packages];
    }

    /**
     * @param  array<int, string>  $packages
     */
    private function printNextSteps(string $stack, string $lang, array $packages): void
    {
        $entry = StackDefinition::entryFile($stack, $lang);

        $this->newLine();
        $this->line('Next steps:');
        $this->line("  1. Update your vite.config.js or vite.config.ts to add the {$stack} Vite plugin and use {$entry} as an input.");
        $this->line("  2. Create your page components under resources/js/Pages so the generated resolver can find them.");
        $this->line('  3. Rebuild your assets with npm run dev or npm run build.');
        $this->line('  4. Publish config later with: php artisan vendor:publish --tag=vormia-inertia-config');
        $this->line('  5. Installed npm package set: ' . implode(', ', $packages));
    }
}
