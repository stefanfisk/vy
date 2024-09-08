<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use StefanFisk\Vy\Hooks\ContextHook;

/**
 * @template TVal
 */
final class Context
{
    /**
     * @param TDefVal $defaultValue
     *
     * @return self<TDefVal>
     *
     * @template TDefVal
     */
    public static function create(mixed $defaultValue = null): self
    {
        return new self($defaultValue);
    }

    /**
     * @param TVal $defaultValue
     */
    private function __construct(
        public readonly mixed $defaultValue,
    ) {
    }

    /**
     * @param TVal $value
     */
    public function el(mixed $value): Element
    {
        return new Element($this, [
            'value' => $value,
        ]);
    }

    /**
     * @return TVal
     */
    public function use(): mixed
    {
        return ContextHook::use($this);
    }
}
