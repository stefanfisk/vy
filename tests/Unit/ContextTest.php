<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Context;
use StefanFisk\Vy\Errors\ContextHasNoDefaultValueException;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;
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
        $context = new Context();

        $el = $context->el('foo');

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

        $context = new Context();

        $el = $context->el('foo')(...$children);

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

        $context = new Context();
        $this->assertNull($this->renderComponent($context->el('foo')));
    }

    public function testRenderCallsContextProviderHook(): void
    {
        $context = new Context();
        $value = new stdClass();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextProviderHook::class, $context, $value)
            ->once()
            ->andReturn(null);

        $this->renderComponent($context->el($value));
    }

    public function testGetDefaultValueReturnsValue(): void
    {
        $val = new Stdclass();

        $context = new Context(fn () => $val);

        $this->assertSame($val, $context->getDefaultValue());
    }

    public function testGetDefaultValueReturnsThrowsIfThereIsNoGetter(): void
    {
        $context = new Context();

        $this->expectException(ContextHasNoDefaultValueException::class);

        $context->getDefaultValue();
    }

    public function testUseCallsContextHookUse(): void
    {
        $context = new Context();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextHook::class, $context)
            ->once()
            ->andReturn(null);

        $context->use();
    }
}
