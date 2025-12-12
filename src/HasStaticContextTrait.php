<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use StefanFisk\Vy\Errors\ContextHasNoDefaultValueException;

/**
 * @template T
 */
trait HasStaticContextTrait
{
    /** @var ?Context<T> */
    private static ?Context $context = null;

    /**
     * @return T
     */
    protected static function getDefaultContextValue(): mixed
    {
        throw new ContextHasNoDefaultValueException(self::getContext());
    }

    /**
     * @return Context<T>
     */
    private static function getContext(): Context
    {
        self::$context ??= new Context(fn () => static::getDefaultContextValue(), static::class);

        return self::$context;
    }

    /**
     * @param T $value
     */
    private static function contextEl(mixed $value): Element
    {
        return self::getContext()->el($value);
    }

    /**
     * @return T
     */
    private static function useContext(): mixed
    {
        return self::getContext()->use();
    }
}
