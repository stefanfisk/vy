<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Unit\Components;

use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;
use StefanFisk\Vy\Tests\Support\FooContext;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    use MocksHookHandlerTrait;

    public function testElCreatesElement(): void
    {
        $this->assertEquals(
            new Element(
                type: FooContext::class,
                key: null,
                props: [
                    'value' => 'foo',
                ],
            ),
            FooContext::el('foo'),
        );
    }

    public function testRenderReturnsChildren(): void
    {
        $context = new FooContext();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->withAnyArgs()
            ->once()
            ->andReturn(null);

        $children = [
            'foo' => 'bar',
            new stdClass(),
        ];

        $this->assertSame(
            $children,
            $context->render(children: $children),
        );
    }

    public function testRenderReturnsNullForEmptyChildren(): void
    {
        $context = new FooContext();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->withAnyArgs()
            ->once()
            ->andReturn(null);

        $this->assertNull($context->render());
    }

    public function testRenderThrowsForUnknownProps(): void
    {
        $context = new FooContext();

        $this->expectException(Error::class);

        $context->render(...['foo' => 'bar']); // @phpstan-ignore-line
    }

    public function testRenderCallsContextProviderHook(): void
    {
        $value = new stdClass();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextProviderHook::class, $value)
            ->once()
            ->andReturn(null);

        $context = new FooContext();

        $context->render(value: $value);
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
