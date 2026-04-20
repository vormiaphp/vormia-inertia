<?php

namespace VormiaPHP\VormiaInertia\Support;

class ApplicationPaths
{
    public function __construct(
        private readonly string $basePath,
    ) {
    }

    public function base(string $path = ''): string
    {
        return $path === '' ? $this->basePath : $this->join($this->basePath, $path);
    }

    public function app(string $path = ''): string
    {
        return $this->base($path === '' ? 'app' : "app/{$path}");
    }

    public function bootstrap(string $path = ''): string
    {
        return $this->base($path === '' ? 'bootstrap' : "bootstrap/{$path}");
    }

    public function config(string $path = ''): string
    {
        return $this->base($path === '' ? 'config' : "config/{$path}");
    }

    public function public(string $path = ''): string
    {
        return $this->base($path === '' ? 'public' : "public/{$path}");
    }

    public function resource(string $path = ''): string
    {
        return $this->base($path === '' ? 'resources' : "resources/{$path}");
    }

    public function manifest(): string
    {
        return $this->bootstrap('cache/vormia-inertia.json');
    }

    public function relative(string $path): string
    {
        $base = rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
    }

    private function join(string ...$segments): string
    {
        $parts = [];

        foreach ($segments as $index => $segment) {
            if ($segment === '') {
                continue;
            }

            $parts[] = $index === 0
                ? rtrim($segment, DIRECTORY_SEPARATOR)
                : trim($segment, DIRECTORY_SEPARATOR);
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
