<?php

namespace VormiaPHP\VormiaInertia\Tests;

use Illuminate\Support\Facades\File;

class UninstallCommandTest extends TestCase
{
    public function test_uninstall_removes_marker_managed_changes_after_install(): void
    {
        $this->artisan('vormia-inertia:install', [
            '--stack' => 'react',
            '--lang' => 'ts',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->artisan('vormia-inertia:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileDoesNotExist($this->hostPath('resources/js/app.tsx'));
        $this->assertFileDoesNotExist($this->hostPath('resources/css/app.css'));
        $this->assertFileDoesNotExist($this->hostPath('resources/views/app.blade.php'));
        $this->assertFileDoesNotExist($this->hostPath('app/Http/Middleware/HandleInertiaRequests.php'));
        $this->assertStringNotContainsString('vormia-inertia:start bootstrap-middleware', File::get($this->hostPath('bootstrap/app.php')));
        $this->assertNotEmpty($this->fakeNpm()->uninstalled);
    }

    public function test_uninstall_is_idempotent(): void
    {
        $this->artisan('vormia-inertia:install', [
            '--stack' => 'vue',
            '--lang' => 'js',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->artisan('vormia-inertia:uninstall', ['--force' => true])->assertSuccessful();
        $this->artisan('vormia-inertia:uninstall', ['--force' => true])->assertSuccessful();

        $this->assertTrue(true);
    }

    public function test_uninstall_preserves_modified_created_files(): void
    {
        $this->artisan('vormia-inertia:install', [
            '--stack' => 'react',
            '--lang' => 'ts',
            '--no-interaction' => true,
        ])->assertSuccessful();

        File::append($this->hostPath('resources/js/app.tsx'), "\n// custom change");

        $this->artisan('vormia-inertia:uninstall', ['--force' => true])->assertSuccessful();

        $this->assertFileExists($this->hostPath('resources/js/app.tsx'));
        $this->assertStringContainsString('// custom change', File::get($this->hostPath('resources/js/app.tsx')));
    }
}
