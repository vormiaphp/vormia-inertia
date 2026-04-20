<?php

namespace VormiaPHP\VormiaInertia\Support;

class NpmCommandResult
{
    public function __construct(
        public readonly bool $successful,
        public readonly string $output = '',
        public readonly string $errorOutput = '',
    ) {
    }
}
