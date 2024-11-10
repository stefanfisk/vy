<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Components;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;

/**
 * @template TVal
 */
final class Context
{
    /**
     * @param TDef $defaultValue
     *
     * @return self<TDef>
     *
     * @template TDef
     */
    public static function create(mixed $defaultValue): mixed
    {
        return new self($defaultValue);
    }

    /**
     * @param TVal $defaultValue
     */
    public function __construct(
        public readonly mixed $defaultValue,
    ) {
    }

    /**
     * @param TVal $value
     * @param ?non-empty-string $key
     */
    public function el(mixed $value, ?string $key = null): Element
    {
        return new Element(
            type: $this->render(...),
            key: $key,
            props: [
                'value' => $value,
            ],
        );
    }

    private function render(mixed $value, mixed $children = null): mixed
    {
        ContextProviderHook::use($this, $value);

        return $children;
    }

    /**
     * @return TVal
     */
    public function use(): mixed
    {
        return ContextHook::use($this);
    }
}
