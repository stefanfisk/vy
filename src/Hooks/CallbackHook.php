<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Renderer;

class CallbackHook extends Hook
{
    /**
     * @param T $fn
     * @param array<mixed> $deps
     *
     * @return T
     *
     * @template T of Closure
     * @psalm-suppress MixedInferredReturnType,MixedReturnStatement
     */
    public static function use(Closure $fn, array $deps): Closure
    {
        // @phpstan-ignore-next-line
        return static::useWith($fn, $deps);
    }

    private mixed $fn;
    /** @var array<mixed> */
    private array $deps;

    /**
     * @param array<mixed> $deps
     */
    public function __construct(
        Renderer $renderer,
        Node $node,
        Closure $fn,
        array $deps,
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $this->fn = $fn;
        $this->deps = $deps;
    }

    public function initialRender(mixed ...$args): mixed
    {
        return $this->fn;
    }

    public function rerender(mixed ...$args): mixed
    {
        /** @var Closure $fn */
        $fn = $args[0];
        /** @var array<mixed> $deps */
        $deps = $args[1];

        if (!$this->renderer->valuesAreEqual($this->deps, $deps)) {
            $this->fn = $fn;
        }

        return $this->fn;
    }
}
