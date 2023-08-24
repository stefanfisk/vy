<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support;

use StefanFisk\PhpReact\Hooks\Hook;

use function end;

class TestHook extends Hook
{
    public static function use(mixed ...$args): mixed
    {
        return static::useWith(...$args);
    }

    public function initialRender(mixed ...$args): mixed
    {
        return end($args) ?: null;
    }

    public function rerender(mixed ...$args): mixed
    {
        return end($args) ?: null;
    }
}
