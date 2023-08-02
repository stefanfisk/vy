<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Hooks;

use Closure;
use StefanFisk\PhpReact\Node;
use StefanFisk\PhpReact\Renderer;
use stdClass;

class EffectHook extends Hook
{
    /**
     * @param Closure():mixed $calculateValue
     * @param array<mixed> $deps
     */
    public static function use(Closure $calculateValue, ?array $deps = null): void
    {
        static::useWith($calculateValue, $deps);
    }

    /** @var Closure():(Closure():void|null) */
    private Closure $nextSetup;
    /** @var Closure():void|null */
    private Closure | null $cleanup = null;
    /** @var array<mixed>|null */
    private array | null $nextDeps;
    /** @var array<mixed>|null */
    private array | null $deps = null;

    /**
     * @param Closure():(Closure():void|null) $setup
     * @param array<mixed> $deps
     */
    public function __construct(
        Renderer $renderer,
        Node $node,
        Closure $setup,
        array | null $deps = [],
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $this->nextSetup = $setup;
        $this->nextDeps = $deps;
        //HACK: Force setup after first render
        $this->deps = [new stdClass()];
    }

    public function initialRender(mixed ...$args): mixed
    {
        return null;
    }

    public function rerender(mixed ...$args): mixed
    {
        /** @var Closure():(Closure():void|null) $nextSetup */
        $nextSetup = $args[0];
        /** @var array<mixed>|null $nextDeps */
        $nextDeps = $args[1];

        $this->nextSetup = $nextSetup;
        $this->nextDeps = $nextDeps;

        return null;
    }

    public function afterRender(): void
    {
        if ($this->deps === $this->nextDeps) {
            return;
        }

        if ($this->cleanup) {
            ($this->cleanup)();
        }

        $this->cleanup = ($this->nextSetup)();
        $this->deps = $this->nextDeps;
    }

    public function unmount(): void
    {
        if ($this->cleanup) {
            ($this->cleanup)();
        }
    }
}
