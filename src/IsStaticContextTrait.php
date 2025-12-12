<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use StefanFisk\Vy\Errors\ContextHasNoDefaultValueException;

/**
 * @template T
 */
trait IsStaticContextTrait
{
    /** @var ?Context<T> */
    private static ?Context $context = null;

    /**
     * @return T
     */
    protected static function getDefaultValue(): mixed
    {
        throw new ContextHasNoDefaultValueException(self::get());
    }

    /**
     * @return Context<T>
     */
    private static function get(): Context
    {
        self::$context ??= new Context(fn () => static::getDefaultValue(), static::class);

        return self::$context;
    }

    /**
     * @param T $value
     */
    public static function el(mixed $value): Element
    {
        return self::get()->el($value);
    }

    /**
     * @return T
     */
    public static function use(): mixed
    {
        return self::get()->use();
    }
}
