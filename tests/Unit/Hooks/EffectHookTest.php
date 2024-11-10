<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Hooks\EffectHook;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\Support\Mocks\Invokable;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksRendererTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(EffectHook::class)]
class EffectHookTest extends TestCase
{
    use MocksInvokablesTrait;
    use MocksHookHandlerTrait;
    use MocksRendererTrait;

    private Node $node;

    private Invokable&MockInterface $setup;
    private Invokable&MockInterface $cleanup;

    /** @var array<mixed> */
    private array $deps;

    private EffectHook $hook;

    protected function setUp(): void
    {
        $this->node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: '',
        );

        $this->setup = $this->createMockInvokable();
        $this->cleanup = $this->createMockInvokable();

        $this->deps = ['foo', new stdClass()];

        $this->hook = new EffectHook(
            renderer: $this->renderer,
            node :$this->node,
            setup: $this->setup->fn,
            deps: $this->deps,
        );
    }

    public function testUseCallsCurrentHandlerUseHook(): void
    {
        $this->hookHandler
            ->shouldReceive('useHook')
            ->once()
            ->with(EffectHook::class, $this->setup->fn, $this->deps);

        EffectHook::use($this->setup->fn, $this->deps);
    }

    public function testRerenderWithSameSetupAndDeps(): void
    {
        $this->setup
            ->shouldReceive('__invoke')
            ->once()
            ->andReturn($this->cleanup->fn);

        $this->cleanup
            ->shouldReceive('__invoke')
            ->once();

        $this->assertNull($this->hook->initialRender($this->setup->fn, $this->deps));

        $this->assertNull($this->hook->rerender($this->setup->fn, $this->deps));

        $this->hook->afterRender();

        $this->hook->unmount();
    }

    public function testRerenderWithNewSetupAndSameDeps(): void
    {
        $this->assertNull($this->hook->initialRender($this->setup->fn, $this->deps));

        $this->assertNull($this->hook->rerender($this->setup->fn, $this->deps));

        $this->setup
            ->shouldReceive('__invoke')
            ->once()
            ->andReturn($this->cleanup->fn);
        $this->cleanup
            ->shouldReceive('__invoke')
            ->once();

        $this->hook->afterRender();

        $this->setup = $this->createMockInvokable();

        $this->assertNull($this->hook->rerender($this->setup->fn, $this->deps));

        $this->hook->afterRender();

        $this->hook->unmount();
    }

    public function testRerenderWithNewSetupAndNewDeps(): void
    {
        $this->assertNull($this->hook->initialRender($this->setup->fn, $this->deps));

        $this->assertNull($this->hook->rerender($this->setup->fn, $this->deps));

        $this->setup
            ->shouldReceive('__invoke')
            ->once()
            ->andReturn($this->cleanup->fn);
        $this->cleanup
            ->shouldReceive('__invoke')
            ->once();

        $this->hook->afterRender();

        $this->setup = $this->createMockInvokable();
        $this->cleanup = $this->createMockInvokable();

        $this->deps = ['bar', new stdClass()];

        $this->setup
            ->shouldReceive('__invoke')
            ->once()
            ->andReturn($this->cleanup->fn);
        $this->cleanup
            ->shouldReceive('__invoke')
            ->once();

        $this->assertNull($this->hook->rerender($this->setup->fn, $this->deps));

        $this->hook->afterRender();

        $this->hook->unmount();
    }
}
