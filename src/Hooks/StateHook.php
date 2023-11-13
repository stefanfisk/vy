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
     * @return array{T,(Closure(T|Closure(T):T):void)}
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
    /** @var Closure(mixed):void */
    private Closure $setValue;
    private bool $hasNextValue;
    private mixed $nextValue;
    /** @var list<Closure(mixed):mixed> */
    private array $setValueQueue = [];

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

    /** @return array{mixed,(Closure(mixed):void)} */
    public function initialRender(mixed ...$args): array
    {
        return [$this->value, $this->setValue];
    }

    /** @return array{mixed,(Closure(mixed):void)} */
    public function rerender(mixed ...$args): array
    {
        // Sync

        if ($this->hasNextValue) {
            $this->value = $this->nextValue;
            $this->nextValue = null;
            $this->hasNextValue = false;
        }

        // Async

        if ($this->setValueQueue) {
            $nextValue = $this->value;

            foreach ($this->setValueQueue as $setValue) {
                $nextValue = $setValue($nextValue);
            }

            $this->value = $nextValue;
            $this->setValueQueue = [];
        }

        // Done

        return [$this->value, $this->setValue];
    }

    private function setValue(mixed $newValue): void
    {
        if ($newValue instanceof Closure) {
            /** @psalm-suppress MixedPropertyTypeCoercion */
            $this->setValueQueue[] = $newValue;

            $this->renderer->enqueueRender($this->node);

            return;
        }

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
