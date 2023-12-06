<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Components;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;

use function StefanFisk\Vy\el;

abstract class Context
{
    public static function el(mixed $value = null): Element
    {
        return el(static::class, [
            'value' => $value,
        ]);
    }

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
