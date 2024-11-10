<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Unit\Components;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;
use StefanFisk\Vy\Tests\Support\FooContext;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\Support\RendersComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    use MocksHookHandlerTrait;
    use RendersComponentsTrait;

    public function testElCreatesElement(): void
    {
        $el = FooContext::el('foo');

        $this->assertEquals(['value' => 'foo'], $el->props);
        $this->assertInstanceOf(Closure::class, $el->type);
    }

    public function testRenderReturnsChildren(): void
    {
        $this->hookHandler
            ->shouldReceive('useHook')
            ->withAnyArgs()
            ->once()
            ->andReturn(null);

        $children = [
            'foo',
            new stdClass(),
        ];

        $el = FooContext::el()(...$children);

        $this->assertSame(
            $children,
            $this->renderComponent($el),
        );
    }

    public function testRenderReturnsNullForEmptyChildren(): void
    {
        $this->hookHandler
            ->shouldReceive('useHook')
            ->withAnyArgs()
            ->once()
            ->andReturn(null);

        $this->assertNull($this->renderComponent(FooContext::el()));
    }

    public function testRenderCallsContextProviderHook(): void
    {
        $value = new stdClass();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextProviderHook::class, FooContext::class, $value)
            ->once()
            ->andReturn(null);

        $this->renderComponent(FooContext::el($value));
    }

    public function testGetDefaultValueReturnsNull(): void
    {
        $context = new class extends Context {
        };

        $this->assertNull($context::getDefaultValue());
    }

    public function testUseCallsContextHookUse(): void
    {
        $context = new class extends Context {
        };

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextHook::class, $context::class)
            ->once()
            ->andReturn(null);

        $context::use();
    }
}
