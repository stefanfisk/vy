<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Hooks;

use Closure;
use StefanFisk\PhpReact\Node;
use StefanFisk\PhpReact\Renderer;

use function array_filter;

class ContextProviderHook extends Hook
{
    public static function use(mixed $value): void
    {
        static::useWith($value);
    }

    private mixed $nextValue;
    private mixed $value;

    /** @var array<Closure(mixed):void> */
    private array $subscribers = [];

    public function __construct(
        Renderer $renderer,
        Node $node,
        mixed $value,
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $this->nextValue = $value;
        $this->value = $this->nextValue;
    }

    public function initialRender(mixed ...$args): mixed
    {
        return null;
    }

    public function rerender(mixed ...$args): mixed
    {
        $value = $args[0] ?? null;

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
