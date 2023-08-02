<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Hooks;

use Closure;
use StefanFisk\PhpReact\Node;
use StefanFisk\PhpReact\Renderer;

class StateHook extends Hook
{
    /**
     * @return array{mixed,Closure(mixed):void}
     *
     * @psalm-suppress MixedInferredReturnType
     */
    public static function use(mixed $initialValue): array
    {
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress MixedInferredReturnType,MixedReturnStatement
         */
        return static::useWith($initialValue);
    }

    private Closure $setValue;
    private mixed $nextValue;
    private mixed $value;

    public function __construct(
        Renderer $renderer,
        Node $node,
        mixed $initialValue,
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $this->setValue = $this->setValue(...);
        $this->nextValue = $initialValue;
        $this->value = $initialValue;
    }

    public function needsRender(): bool
    {
        return $this->value !== $this->nextValue;
    }

    public function initialRender(mixed ...$args): mixed
    {
        return [$this->value, $this->setValue];
    }

    public function rerender(mixed ...$args): mixed
    {
        $this->value = $this->nextValue;

        return [$this->value, $this->setValue];
    }

    private function setValue(mixed $newValue): void
    {
        $this->nextValue = $newValue;

        if ($this->nextValue === $this->value) {
            return;
        }

        $this->renderer->enqueueRender($this->node);
    }
}
