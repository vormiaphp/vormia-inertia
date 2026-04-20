<?php

namespace VormiaPHP\VormiaInertia\Support;

use InvalidArgumentException;

class StackDefinition
{
    /**
     * @return array<int, string>
     */
    public static function stacks(): array
    {
        return ['react', 'vue', 'svelte'];
    }

    /**
     * @return array<int, string>
     */
    public static function languages(): array
    {
        return ['js', 'ts'];
    }

    public static function entryFile(string $stack, string $lang): string
    {
        return "resources/js/" . self::entryBasename($stack, $lang);
    }

    public static function entryBasename(string $stack, string $lang): string
    {
        self::assertValid($stack, $lang);

        return match ([$stack, $lang]) {
            ['react', 'ts'] => 'app.tsx',
            ['react', 'js'] => 'app.jsx',
            ['vue', 'ts'], ['svelte', 'ts'] => 'app.ts',
            default => 'app.js',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function installPackages(string $stack, string $lang): array
    {
        self::assertValid($stack, $lang);

        return match ($stack) {
            'react' => array_values(array_filter([
                '@inertiajs/react',
                'react',
                'react-dom',
                '@vitejs/plugin-react',
                $lang === 'ts' ? 'typescript' : null,
                $lang === 'ts' ? '@types/react' : null,
                $lang === 'ts' ? '@types/react-dom' : null,
            ])),
            'vue' => array_values(array_filter([
                '@inertiajs/vue3',
                'vue',
                '@vitejs/plugin-vue',
                $lang === 'ts' ? 'typescript' : null,
            ])),
            'svelte' => array_values(array_filter([
                '@inertiajs/svelte',
                'svelte',
                '@sveltejs/vite-plugin-svelte',
                $lang === 'ts' ? 'typescript' : null,
            ])),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function acceptedReplaceTargets(string $stack, string $lang): array
    {
        self::assertValid($stack, $lang);

        $targets = ['app.js', 'app.css'];

        return match ([$stack, $lang]) {
            ['react', 'ts'] => [...$targets, 'app.tsx'],
            ['react', 'js'] => [...$targets, 'app.jsx'],
            ['vue', 'ts'], ['svelte', 'ts'] => [...$targets, 'app.ts'],
            default => $targets,
        };
    }

    public static function frameworkPackage(string $stack): string
    {
        self::assertValid($stack);

        return match ($stack) {
            'react' => '@inertiajs/react',
            'vue' => '@inertiajs/vue3',
            'svelte' => '@inertiajs/svelte',
        };
    }

    public static function assertValid(string $stack, ?string $lang = null): void
    {
        if (! in_array($stack, self::stacks(), true)) {
            throw new InvalidArgumentException("Unsupported stack [{$stack}].");
        }

        if ($lang !== null && ! in_array($lang, self::languages(), true)) {
            throw new InvalidArgumentException("Unsupported language [{$lang}].");
        }
    }
}
