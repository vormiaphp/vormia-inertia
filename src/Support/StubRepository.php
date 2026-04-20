<?php

namespace VormiaPHP\VormiaInertia\Support;

use Illuminate\Support\Facades\File;

class StubRepository
{
    public function __construct(
        private readonly string $stubPath,
    ) {
    }

    public function appCss(): string
    {
        return $this->get('resources/css/app.css.stub');
    }

    public function config(): string
    {
        return File::get(__DIR__ . '/../config/vormia-inertia.php');
    }

    public function entry(string $stack, string $lang): string
    {
        $basename = StackDefinition::entryBasename($stack, $lang);

        return $this->get("resources/js/{$stack}/{$basename}.stub");
    }

    public function handleInertiaRequests(): string
    {
        return $this->get('app/Http/Middleware/HandleInertiaRequests.php.stub');
    }

    public function rootView(string $stack, string $lang): string
    {
        $template = $this->get('resources/views/app.blade.php.stub');

        return str_replace(
            ['{{ entry }}', '{{ react_refresh }}'],
            [
                StackDefinition::entryFile($stack, $lang),
                $stack === 'react' ? "    @viteReactRefresh\n" : '',
            ],
            $template,
        );
    }

    public function matchesManagedFile(string $contents): bool
    {
        return str_contains($contents, MarkerPatcher::MANAGED_FILE_MARKER);
    }

    private function get(string $relativePath): string
    {
        return File::get(rtrim($this->stubPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    }
}
