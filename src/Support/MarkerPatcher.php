<?php

namespace VormiaPHP\VormiaInertia\Support;

class MarkerPatcher
{
    public const MANAGED_FILE_MARKER = 'vormia-inertia:managed-file';

    public static function commentBlock(string $label, string $content, string $prefix = '//'): string
    {
        $content = rtrim($content);

        return "{$prefix} vormia-inertia:start {$label}\n{$content}\n{$prefix} vormia-inertia:end {$label}";
    }

    public static function hasBlock(string $content, string $label): bool
    {
        return str_contains($content, "vormia-inertia:start {$label}");
    }

    public static function removeBlock(string $content, string $label): string
    {
        $pattern = sprintf('/^[^\r\n]*vormia-inertia:start %s[^\r\n]*\R.*?^[^\r\n]*vormia-inertia:end %s[^\r\n]*\R?/ms', preg_quote($label, '/'), preg_quote($label, '/'));

        return preg_replace($pattern, '', $content) ?? $content;
    }
}
