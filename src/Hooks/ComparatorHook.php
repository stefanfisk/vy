<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;

class ComparatorHook extends Hook
{
    /**
     * @return Closure(mixed,mixed):bool
     *
     * @psalm-suppress MixedInferredReturnType,MixedReturnStatement
     */
    public static function use(): Closure
    {
        // @phpstan-ignore-next-line
        return static::useWith();
    }

    public function initialRender(mixed ...$args): mixed
    {
        return fn (mixed $a, mixed $b) => $this->renderer->valuesAreEqual($a, $b);
    }

    public function rerender(mixed ...$args): mixed
    {
        return fn (mixed $a, mixed $b) => $this->renderer->valuesAreEqual($a, $b);
    }
}
