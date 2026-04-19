<?php

namespace VormiaPHP\VormiaInertia\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string name()
 *
 * @see \VormiaPHP\VormiaInertia\VormiaInertia
 */
class VormiaInertia extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vormia.inertia';
    }
}
