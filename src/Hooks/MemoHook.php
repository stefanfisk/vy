<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Renderer;

class MemoHook extends Hook
{
    /**
     * @param Closure():T $calculateValue
     * @param array<mixed> $deps
     *
     * @return T
     *
     * @template T
     * @psalm-suppress MixedInferredReturnType,MixedReturnStatement
     */
    public static function use(Closure $calculateValue, array $deps): mixed
    {
        return static::useWith($calculateValue, $deps);
    }

    private mixed $value;
    /** @var array<mixed> */
    private array $deps;

    /**
     * @param Closure():mixed $calculateValue
     * @param array<mixed> $deps
     */
    public function __construct(
        Renderer $renderer,
        Node $node,
        Closure $calculateValue,
        array $deps,
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $this->value = $calculateValue();
        $this->deps = $deps;
    }

    public function initialRender(mixed ...$args): mixed
    {
        return $this->value;
    }

    public function rerender(mixed ...$args): mixed
    {
        /** @var Closure():mixed $calculateValue */
        $calculateValue = $args[0];
        /** @var array<mixed> $deps */
        $deps = $args[1];

        if ($deps !== $this->deps) {
            $this->value = $calculateValue();
        }

        return $this->value;
    }
}
