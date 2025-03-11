<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Components;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;

abstract class Context
{
    public static function el(mixed $value = null): Element
    {
        return Element::create(self::render(...), [
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

    private static function render(
        mixed $value = null,
        mixed $children = null,
    ): mixed {
        ContextProviderHook::use(static::class, $value);

        return $children;
    }
}
