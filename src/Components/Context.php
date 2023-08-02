<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Components;

use StefanFisk\PhpReact\Hooks\ContextHook;
use StefanFisk\PhpReact\Hooks\ContextProviderHook;

/** @psalm-api */
abstract class Context
{
    public static function getDefaultValue(): mixed
    {
        return null;
    }

    public static function use(): mixed
    {
        return ContextHook::use(static::class);
    }

    final public function __construct()
    {
    }

    final public function render(mixed $value = null, mixed $children = null): mixed
    {
        ContextProviderHook::use($value);

        return $children;
    }
}
