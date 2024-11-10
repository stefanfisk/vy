<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Errors\HookException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Renderer;

use function array_filter;

class ContextProviderHook extends Hook
{
    /**
     * @param class-string<Context> $context
     */
    public static function use(string $context, mixed $value): void
    {
        static::useWith($context, $value);
    }

    /** @var class-string<Context> */
    public readonly string $context;

    private mixed $nextValue;
    private mixed $value;

    /** @var array<Closure(mixed):void> */
    private array $subscribers = [];

    /**
     * @param class-string<Context> $context
     */
    public function __construct(
        Renderer $renderer,
        Node $node,
        string $context,
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

    public function initialRender(mixed ...$args): mixed
    {
        return null;
    }

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
