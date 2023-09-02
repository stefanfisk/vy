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

    private mixed $value;
    private Closure $setValue;
    private bool $hasNextValue;
    private mixed $nextValue;

    public function __construct(
        Renderer $renderer,
        Node $node,
        mixed $initialValue,
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $this->value = $initialValue;
        $this->setValue = $this->setValue(...);
        $this->hasNextValue = false;
        $this->nextValue = null;
    }

    public function needsRender(): bool
    {
        return $this->hasNextValue;
    }

    public function initialRender(mixed ...$args): mixed
    {
        return [$this->value, $this->setValue];
    }

    public function rerender(mixed ...$args): mixed
    {
        if ($this->hasNextValue) {
            $this->value = $this->nextValue;
            $this->nextValue = null;
            $this->hasNextValue = false;
        }

        return [$this->value, $this->setValue];
    }

    private function setValue(mixed $newValue): void
    {
        if ($this->renderer->valuesAreEqual($this->value, $newValue)) {
            $this->nextValue = null;
            $this->hasNextValue = false;

            return;
        }

        $this->nextValue = $newValue;
        $this->hasNextValue = true;

        $this->renderer->enqueueRender($this->node);
    }
}
