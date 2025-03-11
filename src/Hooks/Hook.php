<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use StefanFisk\Vy\Errors\RenderException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\RendererInterface;

use function array_pop;
use function assert;
use function end;

abstract class Hook
{
    /** @var list<HookHandlerInterface> */
    private static array $handlerStack = [];

    public static function getHandler(): ?HookHandlerInterface
    {
        return end(self::$handlerStack) ?: null;
    }

    public static function pushHandler(HookHandlerInterface $handler): void
    {
        assert($handler !== end(self::$handlerStack));

        self::$handlerStack[] = $handler;
    }

    public static function popHandler(): ?HookHandlerInterface
    {
        return array_pop(self::$handlerStack);
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
        public readonly RendererInterface $renderer,
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
