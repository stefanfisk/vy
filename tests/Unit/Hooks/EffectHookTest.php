<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Hooks\EffectHook;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Tests\Support\Mocks\MockInvokable;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksRendererTrait;
use StefanFisk\PhpReact\Tests\TestCase;
use stdClass;

#[CoversClass(EffectHook::class)]
class EffectHookTest extends TestCase
{
    use MocksInvokablesTrait;
    use MocksHookHandlerTrait;
    use MocksRendererTrait;

    private Node $node;

    private MockInvokable $setupMock;
    private Closure $setup;
    private MockInvokable $cleanupMock;
    private Closure $cleanup;

    /** @var array<mixed> */
    private array $deps;

    private EffectHook $hook;

    protected function setUp(): void
    {
        $this->node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: null,
            component: null,
        );

        $this->setupMock = $this->createInvokableMock();
        $this->setup = ($this->setupMock)(...);
        $this->cleanupMock = $this->createInvokableMock();
        $this->cleanup = ($this->cleanupMock)(...);

        $this->deps = ['foo', new stdClass()];

        $this->hook = new EffectHook(
            renderer: $this->renderer,
            node :$this->node,
            setup: $this->setup(...), // @phpstan-ignore-line
            deps: $this->deps,
        );
    }

    public function testUseCallsCurrentHandlerUseHook(): void
    {
        $this->hookHandler
            ->expects($this->once())
            ->method('useHook')
            ->with(EffectHook::class, $this->setup, $this->deps);

        EffectHook::use($this->setup, $this->deps);
    }

    public function testRerenderWithSameSetupAndDeps(): void
    {
        $this->setupMock
            ->expects($this->once())
            ->willReturn($this->cleanup);

        $this->cleanupMock->expects($this->once());

        $this->assertNull($this->hook->initialRender(($this->setup)(...), $this->deps));

        $this->assertNull($this->hook->rerender($this->setup, $this->deps));

        $this->hook->afterRender();

        $this->hook->unmount();
    }

    public function testRerenderWithNewSetupAndSameDeps(): void
    {
        $this->assertNull($this->hook->initialRender($this->setup, $this->deps));

        $this->assertNull($this->hook->rerender($this->setup, $this->deps));

        $this->setupMock
            ->expects($this->once())
            ->willReturn($this->cleanup);
        $this->cleanupMock->expects($this->once());

        $this->hook->afterRender();

        $this->setupMock = $this->createInvokableMock();
        $this->setup = ($this->setupMock)(...);

        $this->assertNull($this->hook->rerender($this->setup, $this->deps));

        $this->hook->afterRender();

        $this->hook->unmount();
    }

    public function testRerenderWithNewSetupAndNewDeps(): void
    {
        $this->assertNull($this->hook->initialRender($this->setup, $this->deps));

        $this->assertNull($this->hook->rerender($this->setup, $this->deps));

        $this->setupMock
            ->expects($this->once())
            ->willReturn($this->cleanup);
        $this->cleanupMock->expects($this->once());

        $this->hook->afterRender();

        $this->setupMock = $this->createInvokableMock();
        $this->setup = ($this->setupMock)(...);
        $this->cleanupMock = $this->createInvokableMock();
        $this->cleanup = ($this->cleanupMock)(...);

        $this->deps = ['bar', new stdClass()];

        $this->setupMock
            ->expects($this->once())
            ->willReturn($this->cleanup);
        $this->cleanupMock->expects($this->once());

        $this->assertNull($this->hook->rerender($this->setup, $this->deps));

        $this->hook->afterRender();

        $this->hook->unmount();
    }
}
