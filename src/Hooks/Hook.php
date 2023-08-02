<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Hooks;

use StefanFisk\PhpReact\Errors\RenderException;
use StefanFisk\PhpReact\Node;
use StefanFisk\PhpReact\Renderer;

use function assert;

abstract class Hook
{
    private static HookHandlerInterface | null $handler = null;

    public static function getHandler(): HookHandlerInterface | null
    {
        return self::$handler;
    }

    public static function setHandler(HookHandlerInterface | null $handler): void
    {
        assert(!(self::$handler === null && $handler === null));

        self::$handler = $handler;
    }

    final protected static function useWith(mixed ...$args): mixed
    {
        $handler = self::getHandler();

        if (!$handler) {
            throw new RenderException(
                message: 'Cannot call hooks outside of component render.',
            );
        }

        return $handler->useHook(static::class, ...$args);
    }

    public function __construct(
        public readonly Renderer $renderer,
        public readonly Node $node,
    ) {
    }

    public function needsRender(): bool
    {
        return false;
    }

    abstract public function initialRender(mixed ...$args): mixed;

    abstract public function rerender(mixed ...$args): mixed;

    public function afterRender(): void
    {
    }

    public function unmount(): void
    {
    }
}
