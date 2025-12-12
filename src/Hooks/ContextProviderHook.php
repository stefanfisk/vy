<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;
use Override;
use StefanFisk\Vy\Context;
use StefanFisk\Vy\Errors\HookException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Renderer;

use function array_filter;

/**
 * @template T
 */
final class ContextProviderHook extends Hook
{
    /**
     * @param Context<TVal> $context
     *
     * @template TVal
     */
    public static function use(Context $context, mixed $value): void
    {
        static::useWith($context, $value);
    }

    /** @var Context<T> */
    public readonly Context $context;

    private mixed $nextValue;
    private mixed $value;

    /** @var array<Closure(mixed):void> */
    private array $subscribers = [];

    /**
     * @param Context<T> $context
     */
    public function __construct(
        Renderer $renderer,
        Node $node,
        Context $context,
        mixed $value,
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $this->context = $context;

        $this->nextValue = $value;
        $this->value = $this->nextValue;
    }

    #[Override]
    public function initialRender(mixed ...$args): mixed
    {
        return null;
    }

    #[Override]
    public function rerender(mixed ...$args): mixed
    {
        $context = $args[0] ?? null;
        $value = $args[1] ?? null;

        if ($context !== $this->context) {
            throw new HookException(
                message: 'ContextProviderHook::use() must be called with the same context on every render.',
                hook: self::class,
                node: $this->node,
            );
        }

        $this->nextValue = $value;

        return null;
    }

    #[Override]
    public function afterRender(): void
    {
        if ($this->nextValue === $this->value) {
            return;
        }

        $this->value = $this->nextValue;

        foreach ($this->subscribers as $subscriber) {
            $subscriber($this->value);
        }
    }

    /** @param Closure(mixed):void $subscriber */
    public function subscribe(Closure $subscriber): Closure
    {
        $this->subscribers[] = $subscriber;

        return fn () => $this->unsubscribe($subscriber);
    }

    /** @param Closure(mixed):void $subscriber */
    private function unsubscribe(Closure $subscriber): void
    {
        $this->subscribers = array_filter($this->subscribers, fn ($s) => $s !== $subscriber);
    }
}
