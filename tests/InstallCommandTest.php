<?php

namespace VormiaPHP\VormiaInertia\Tests;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;

class InstallCommandTest extends TestCase
{
    public function test_install_command_prompts_for_stack_and_language_when_missing(): void
    {
        $this->artisan('vormia-inertia:install')
            ->expectsChoice('Which Inertia client stack would you like to install?', 'react', ['react', 'vue', 'svelte'])
            ->expectsChoice('Which entry language would you like to scaffold?', 'ts', ['js', 'ts'])
            ->assertSuccessful();

        $this->assertFileExists($this->hostPath('resources/js/app.tsx'));
        $this->assertStringContainsString('@inertiajs/react', File::get($this->hostPath('resources/js/app.tsx')));
        $this->assertFileExists($this->hostPath('app/Http/Middleware/HandleInertiaRequests.php'));
        $this->assertFileExists($this->hostPath('resources/views/app.blade.php'));
        $this->assertNotEmpty($this->fakeNpm()->installed);
    }

    public function test_install_command_requires_stack_and_lang_without_interaction(): void
    {
        $this->artisan('vormia-inertia:install', ['--no-interaction' => true])
            ->assertFailed();
    }

    #[DataProvider('replaceTargetMatrix')]
    public function test_replace_app_js_alias_maps_to_the_selected_entry_file(string $stack, string $lang, string $expectedRelativePath, string $expectedPackage): void
    {
        $this->putHostFile($expectedRelativePath, '// existing entry');

        $this->artisan('vormia-inertia:install', [
            '--stack' => $stack,
            '--lang' => $lang,
            '--replace' => ['app.js'],
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString($expectedPackage, File::get($this->hostPath($expectedRelativePath)));
    }

    public function test_replace_app_css_flag_replaces_existing_css_file(): void
    {
        $this->putHostFile('resources/css/app.css', 'body { color: red; }');

        $this->artisan('vormia-inertia:install', [
            '--stack' => 'react',
            '--lang' => 'ts',
            '--replace' => ['app.css'],
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('vormia-inertia:managed-file app-css', File::get($this->hostPath('resources/css/app.css')));
    }

    public function test_install_does_not_overwrite_existing_entry_assets_without_replace_flags(): void
    {
        $this->putHostFile('resources/js/app.tsx', '// keep me');
        $this->putHostFile('resources/css/app.css', 'body { color: red; }');

        $this->artisan('vormia-inertia:install', [
            '--stack' => 'react',
            '--lang' => 'ts',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertSame('// keep me', File::get($this->hostPath('resources/js/app.tsx')));
        $this->assertSame('body { color: red; }', File::get($this->hostPath('resources/css/app.css')));
    }

    public function test_explicit_extension_replace_alias_is_supported(): void
    {
        $this->putHostFile('resources/js/app.tsx', '// existing entry');

        $this->artisan('vormia-inertia:install', [
            '--stack' => 'react',
            '--lang' => 'ts',
            '--replace' => ['app.tsx'],
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('@inertiajs/react', File::get($this->hostPath('resources/js/app.tsx')));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function replaceTargetMatrix(): array
    {
        return [
            'react-ts' => ['react', 'ts', 'resources/js/app.tsx', '@inertiajs/react'],
            'react-js' => ['react', 'js', 'resources/js/app.jsx', '@inertiajs/react'],
            'vue-ts' => ['vue', 'ts', 'resources/js/app.ts', '@inertiajs/vue3'],
            'vue-js' => ['vue', 'js', 'resources/js/app.js', '@inertiajs/vue3'],
            'svelte-ts' => ['svelte', 'ts', 'resources/js/app.ts', '@inertiajs/svelte'],
            'svelte-js' => ['svelte', 'js', 'resources/js/app.js', '@inertiajs/svelte'],
        ];
    }
}
