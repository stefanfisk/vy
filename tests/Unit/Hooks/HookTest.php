<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Errors\RenderException;
use StefanFisk\Vy\Hooks\Hook;
use StefanFisk\Vy\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksRendererTrait;
use StefanFisk\Vy\Tests\Support\TestHook;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Hook::class)]
class HookTest extends TestCase
{
    use CreatesStubNodesTrait;
    use MocksHookHandlerTrait;
    use MocksRendererTrait;

    public function testUseWithCallsCurrentRendererUseHook(): void
    {
        $arg0 = 'foo';
        $arg1 = new stdClass();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->once()
            ->with(TestHook::class, $arg0, $arg1)
            ->andReturn($arg1);

        $this->assertSame(
            $arg1,
            TestHook::use($arg0, $arg1),
        );
    }

    public function testUseWithThrowsWhenThereIsNoCurrentHandler(): void
    {
        Hook::popHandler();

        $this->expectException(RenderException::class);

        try {
            TestHook::use();
        } finally {
            Hook::pushHandler($this->hookHandler);
        }
    }

    public function testNeedsRenderReturnsFalse(): void
    {
        $hook = new TestHook(
            renderer: $this->renderer,
            node: $this->createStubNode(),
        );

        $this->assertFalse($hook->needsRender());
    }

    public function testAfterRenderDoesNothing(): void
    {
        $this->expectNotToPerformAssertions();

        $hook = new TestHook(
            renderer: $this->renderer,
            node: $this->createStubNode(),
        );

        $hook->afterRender();
    }

    public function testUnmountDoesNothing(): void
    {
        $this->expectNotToPerformAssertions();

        $hook = new TestHook(
            renderer: $this->renderer,
            node: $this->createStubNode(),
        );

        $hook->unmount();
    }
}
