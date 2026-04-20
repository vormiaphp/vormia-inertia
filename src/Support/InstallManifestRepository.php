<?php

namespace VormiaPHP\VormiaInertia\Support;

use Illuminate\Support\Facades\File;

class InstallManifestRepository
{
    public function __construct(
        private readonly ApplicationPaths $paths,
    ) {
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    public function write(array $manifest): void
    {
        File::ensureDirectoryExists(dirname($this->paths->manifest()));
        File::put($this->paths->manifest(), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(): ?array
    {
        if (! File::exists($this->paths->manifest())) {
            return null;
        }

        $manifest = json_decode(File::get($this->paths->manifest()), true);

        return is_array($manifest) ? $manifest : null;
    }

    public function delete(): void
    {
        File::delete($this->paths->manifest());
    }
}
