<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use StefanFisk\Vy\Errors\ContextHasNoDefaultValueException;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;

use function spl_object_id;

/**
 * @template T
 */
final class Context
{
    /** @var non-empty-string $name */
    public readonly string $name;

    /**
     * @param ?Closure():T $getDefaultValue
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly ?Closure $getDefaultValue = null,
        ?string $name = null,
    ) {
        $this->name = $name ?? (string) spl_object_id($this);
    }

    /**
     * @param T $value
     */
    public function el(mixed $value): Element
    {
        return Element::create(static::render(...), [
            'value' => $value,
        ]);
    }

    /**
     * @return T
     */
    public function getDefaultValue(): mixed
    {
        if ($this->getDefaultValue === null) {
            throw new ContextHasNoDefaultValueException($this);
        }

        return ($this->getDefaultValue)();
    }

    /**
     * @return T
     */
    public function use(): mixed
    {
        return ContextHook::use($this);
    }

    /**
     * @param T $value
     */
    private function render(
        mixed $value,
        mixed $children = null,
    ): mixed {
        ContextProviderHook::use($this, $value);

        return $children;
    }
}
