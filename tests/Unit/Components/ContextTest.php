<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Unit\Components;

use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Components\Context;
use StefanFisk\PhpReact\Hooks\ContextHook;
use StefanFisk\PhpReact\Hooks\ContextProviderHook;
use StefanFisk\PhpReact\Tests\Support\FooContext;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksHookHandlerTrait;
use stdClass;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    use MocksHookHandlerTrait;

    public function testRenderReturnsChildren(): void
    {
        $context = new FooContext();

        $this->hookHandler
            ->expects($this->once())
            ->method('useHook')
            ->with($this->anything())
            ->willReturn(null);

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
            ->expects($this->once())
            ->method('useHook')
            ->with($this->anything())
            ->willReturn(null);

        $this->assertNull($context->render());
    }

    public function testRenderThrowsForUnknownProps(): void
    {
        $context = new FooContext();

        $this->expectException(Error::class);

        $context->render(...['foo' => 'bar']);
    }

    public function testRenderCallsContextProviderHook(): void
    {
        $value = new stdClass();

        $this->hookHandler
            ->expects($this->once())
            ->method('useHook')
            ->with(ContextProviderHook::class, $value)
            ->willReturn(null);

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
        $this->hookHandler
            ->expects($this->once())
            ->method('useHook')
            ->with(ContextHook::class)
            ->willReturn(null);

        $context = new class extends Context {
        };

        $context::use();
    }
}
