<?php

namespace VormiaPHP\VormiaInertia\Tests;

use Illuminate\Support\Facades\File;

class DocumentationTest extends TestCase
{
    public function test_readme_mentions_commands_replace_flags_and_helpers(): void
    {
        $readme = File::get(__DIR__ . '/../README.md');
        $guide = File::get(__DIR__ . '/../vormia-inertia.md');

        foreach ([
            'vormia-inertia:install',
            'vormia-inertia:uninstall',
            '--replace=app.js',
            '--replace=app.css',
            'VormiaInertia::name()',
            "app('vormia.inertia')",
            'NotificationService::current()',
            'MediaForge::url($path)->public()',
            'MediaForge::url($path)->private()',
            'usePage',
            'useForm',
            'router.visit',
        ] as $needle) {
            $this->assertStringContainsString($needle, $readme);
            $this->assertStringContainsString($needle, $guide);
        }
    }
}
