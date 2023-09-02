<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Renderer;

class StateHook extends Hook
{
    /**
     * @param T $initialValue
     *
     * @return array{T,(Closure(T):void)}
     *
     * @template T
     * @psalm-suppress MixedInferredReturnType,MixedReturnStatement
     */
    public static function use(mixed $initialValue): array
    {
        // @phpstan-ignore-next-line
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
