<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use StefanFisk\PhpReact\Serialization\Html\Middleware\TransformingMiddleware;
use StefanFisk\PhpReact\Tests\Support\Mocks\MockInvokable;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\PhpReact\Tests\TestCase;

#[CoversClass(TransformingMiddleware::class)]
class TransformingMiddlewareTest extends TestCase
{
    use MocksInvokablesTrait;

    private TransformingMiddleware&MockObject $middleware;
    private MockInvokable $next;

    protected function setUp(): void
    {
        $this->middleware = $this->createPartialMock(TransformingMiddleware::class, [
            'transformValue',
        ]);
        $this->next = $this->createInvokableMock();
    }

    public function testCallsTransformValueForAttributes(): void
    {
        $this->middleware
            ->expects($this->once())
            ->method('transformValue')
            ->with('bar')
            ->willReturn('baz');

        $this->next
            ->expects($this->once())
            ->with($this->anything())
            ->willReturnArgument(1);

        $this->middleware->processAttributeValue(
            name: 'foo',
            value: 'bar',
            next: ($this->next)(...),
        );
    }

    public function testCallsTransformValueForNode(): void
    {
        $this->middleware
            ->expects($this->once())
            ->method('transformValue')
            ->with('foo')
            ->willReturn('bar');

        $this->next
            ->expects($this->once())
            ->with($this->anything())
            ->willReturnArgument(1);

        $this->middleware->processNodeValue(
            value: 'foo',
            next: ($this->next)(...),
        );
    }
}
