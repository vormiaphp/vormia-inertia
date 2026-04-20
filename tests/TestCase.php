<?php

namespace VormiaPHP\VormiaInertia\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use VormiaPHP\VormiaInertia\Contracts\NpmPackageManager;
use VormiaPHP\VormiaInertia\Support\ApplicationPaths;
use VormiaPHP\VormiaInertia\VormiaInertiaServiceProvider;

abstract class TestCase extends Orchestra
{
    protected string $workingPath;

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            VormiaInertiaServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->workingPath = sys_get_temp_dir() . '/vormia-inertia-tests/' . bin2hex(random_bytes(8));
        File::deleteDirectory($this->workingPath);
        File::ensureDirectoryExists($this->workingPath);

        $this->seedHostApplication();

        $this->app->instance(ApplicationPaths::class, new ApplicationPaths($this->workingPath));
        $this->app->instance(NpmPackageManager::class, new Fakes\FakeNpmPackageManager());
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->workingPath);

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $files
     */
    protected function seedHostApplication(array $files = []): void
    {
        $defaults = [
            'bootstrap/app.php' => <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
PHP,
            'package.json' => <<<'JSON'
{
    "private": true,
    "type": "module"
}
JSON,
            'routes/web.php' => "<?php\n",
            'routes/console.php' => "<?php\n",
        ];

        foreach (array_merge($defaults, $files) as $relativePath => $contents) {
            $this->putHostFile($relativePath, $contents);
        }
    }

    protected function putHostFile(string $relativePath, string $contents): void
    {
        $path = $this->workingPath . '/' . $relativePath;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    protected function hostPath(string $relativePath = ''): string
    {
        return $relativePath === ''
            ? $this->workingPath
            : $this->workingPath . '/' . $relativePath;
    }

    protected function fakeNpm(): Fakes\FakeNpmPackageManager
    {
        return $this->app->make(NpmPackageManager::class);
    }
}
