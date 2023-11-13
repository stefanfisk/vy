<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Hooks\StateHook;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksRendererTrait;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(StateHook::class)]
class StateHookTest extends TestCase
{
    use CreatesStubNodesTrait;
    use MocksHookHandlerTrait;
    use MocksRendererTrait;
    use MocksInvokablesTrait;

    private Node $node;
    private StateHook $hook;

    protected function setUp(): void
    {
        $this->node = $this->createStubNode();

        $this->hook = new StateHook(
            renderer: $this->renderer,
            node: $this->node,
            initialValue: 'foo',
        );
    }

    public function testUseCallsCurrentHandlerUseHook(): void
    {
        $this->hookHandler
            ->shouldReceive('useHook')
            ->once()
            ->with(StateHook::class, 'foo')
            ->andReturn([
                'foo',
                function () {
                },
            ]);

        StateHook::use('foo');
    }

    public function testReturnsInitialValueOnInitialRender(): void
    {
        $ret = $this->hook->initialRender('foo');

        $this->assertIsArray($ret);
        $this->assertCount(2, $ret);
        $this->assertSame('foo', $ret[0]);
        $this->assertInstanceOf(Closure::class, $ret[1]);
    }

    public function testReturnsInitialValueOnRerender(): void
    {
        $ret = $this->hook->initialRender('foo');
        $ret = $this->hook->rerender('foo');

        $this->assertIsArray($ret);
        $this->assertCount(2, $ret);
        $this->assertSame('foo', $ret[0]);
        $this->assertInstanceOf(Closure::class, $ret[1]);
    }

    public function testIgnoresPassedValueOnRerender(): void
    {
        $this->hook->initialRender('foo');

        /** @var string $value */
        [$value] = $this->hook->rerender('bar');

        $this->assertSame('foo', $value);
    }

    public function testReturnsNewValueOnRerender(): void
    {
        /** @var Closure(string):void $setValue */
        [, $setValue] = $this->hook->initialRender('foo');

        $this->renderer
            ->shouldReceive('valuesAreEqual')
            ->once()
            ->with('foo', 'bar')
            ->andReturn(false);

        $this->renderer
            ->shouldReceive('enqueueRender')
            ->once()
            ->with($this->node);

        $setValue('bar');

        [$value] = $this->hook->rerender('foo');

        $this->assertSame('bar', $value);
    }

    public function testDoesNotNeedRenderAfterInitialRender(): void
    {
        $this->hook->initialRender('foo');

        $this->assertFalse($this->hook->needsRender());
    }

    public function testDoesNotNeedRenderAfterSettingEqualValue(): void
    {
        /** @var Closure(string):void $setValue */
        [, $setValue] = $this->hook->initialRender('foo');

        $this->renderer
            ->shouldReceive('valuesAreEqual')
            ->once()
            ->with('foo', 'bar')
            ->andReturn(true);

        $setValue('bar');

        $this->assertFalse($this->hook->needsRender());
    }

    public function testNeedsRenderAfterSettingNonEqualValue(): void
    {
        /** @var Closure(string):void $setValue */
        [, $setValue] = $this->hook->initialRender('foo');

        $this->renderer
            ->shouldReceive('valuesAreEqual')
            ->once()
            ->with('foo', 'bar')
            ->andReturn(false);

        $this->renderer
            ->shouldReceive('enqueueRender')
            ->once()
            ->with($this->node);

        $setValue('bar');

        $this->assertTrue($this->hook->needsRender());
    }

    public function testDoesNotNeedRenderAfterSettingOldValueAfterNewValue(): void
    {
        /** @var Closure(string):void $setValue */
        [, $setValue] = $this->hook->initialRender('foo');

        $this->renderer
            ->shouldReceive('valuesAreEqual')
            ->once()
            ->with('foo', 'bar')
            ->andReturn(false);

        $this->renderer
            ->shouldReceive('enqueueRender')
            ->once()
            ->with($this->node);

        $this->renderer
            ->shouldReceive('valuesAreEqual')
            ->once()
            ->with('foo', 'foo')
            ->andReturn(true);

        $setValue('bar');
        $setValue('foo');

        $this->assertFalse($this->hook->needsRender());
    }

    public function testAsyncSet(): void
    {
        /** @var Closure(Closure):void $setValue */
        [, $setValue] = $this->hook->initialRender('foo');

        $fn = $this->createMockInvokable();

        $this->renderer
            ->shouldReceive('enqueueRender')
            ->times(3)
            ->with($this->node);

        $setValue($fn(...));
        $setValue($fn(...));
        $setValue($fn(...));

        $fn
            ->expects('__invoke')
            ->once()
            ->with('foo')
            ->andReturn('bar');

        $fn
            ->expects('__invoke')
            ->once()
            ->with('bar')
            ->andReturn('baz');

        $fn
            ->expects('__invoke')
            ->once()
            ->with('baz')
            ->andReturn('qux');

        [$newState, $newSetValue] = $this->hook->rerender();

        $this->assertSame('qux', $newState);
        $this->assertSame($setValue, $newSetValue);
    }
}
