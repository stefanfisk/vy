<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;
use Override;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\RendererInterface;
use stdClass;

final class EffectHook extends Hook
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
    private ?Closure $cleanup = null;
    /** @var ?array<mixed> */
    private ?array $nextDeps;
    /** @var ?array<mixed> */
    private ?array $deps = null;

    /**
     * @param Closure():(Closure():void|null) $setup
     * @param array<mixed> $deps
     */
    public function __construct(
        RendererInterface $renderer,
        Node $node,
        Closure $setup,
        ?array $deps = [],
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

    #[Override]
    public function initialRender(mixed ...$args): mixed
    {
        return null;
    }

    #[Override]
    public function rerender(mixed ...$args): mixed
    {
        /** @var Closure():(Closure():void|null) $nextSetup */
        $nextSetup = $args[0];
        /** @var ?array<mixed> $nextDeps */
        $nextDeps = $args[1];

        $this->nextSetup = $nextSetup;
        $this->nextDeps = $nextDeps;

        return null;
    }

    #[Override]
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

    #[Override]
    public function unmount(): void
    {
        if ($this->cleanup) {
            ($this->cleanup)();
        }
    }
}
