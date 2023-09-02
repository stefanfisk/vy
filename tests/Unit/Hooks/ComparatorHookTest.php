<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Hooks\ComparatorHook;
use StefanFisk\Vy\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksRendererTrait;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(ComparatorHook::class)]
class ComparatorHookTest extends TestCase
{
    use CreatesStubNodesTrait;
    use MocksHookHandlerTrait;
    use MocksRendererTrait;

    private ComparatorHook $hook;

    protected function setUp(): void
    {
        $this->hook = new ComparatorHook(
            renderer: $this->renderer,
            node: $this->createStubNode(),
        );
    }

    public function testUseCallsCurrentHandlerUseHook(): void
    {
        $ret = fn () => false;

        $this->hookHandler
            ->shouldReceive('useHook')
            ->once()
            ->with(ComparatorHook::class)
            ->andReturn($ret);

        $this->assertSame(
            $ret,
            ComparatorHook::use(),
        );
    }

    public function testInitialRender(): void
    {
        $ret = $this->hook->initialRender();

        $this->assertInstanceOf(Closure::class, $ret);

        $this->renderer
            ->shouldReceive('valuesAreEqual')
            ->once()
            ->with('foo', 'bar')
            ->andReturn(true);

        $this->assertTrue($ret('foo', 'bar'));
    }

    public function testRerender(): void
    {
        $ret = $this->hook->rerender();

        $this->assertInstanceOf(Closure::class, $ret);

        $this->renderer
            ->shouldReceive('valuesAreEqual')
            ->once()
            ->with('foo', 'bar')
            ->andReturn(true);

        $this->assertTrue($ret('foo', 'bar'));
    }
}
