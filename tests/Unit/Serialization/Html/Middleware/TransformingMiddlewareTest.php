<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Serialization\Html\Middleware\TransformingMiddleware;
use StefanFisk\PhpReact\Tests\Support\Mocks\Invokable;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\PhpReact\Tests\TestCase;

#[CoversClass(TransformingMiddleware::class)]
class TransformingMiddlewareTest extends TestCase
{
    use MocksInvokablesTrait;

    private TransformingMiddleware&MockInterface $middleware;
    private Invokable&MockInterface $next;

    protected function setUp(): void
    {
        $this->middleware = $this->mockery(TransformingMiddleware::class)
            ->makePartial();
        $this->next = $this->createMockInvokable();
    }

    public function testCallsTransformValueForAttributes(): void
    {
        $this->middleware
            ->shouldReceive('transformValue')
            ->once()
            ->with('bar')
            ->andReturn('baz');

        $this->next
            ->shouldReceive('__invoke')
            ->withAnyArgs()
            ->once()
            ->andReturnArg(0);

        $ret = $this->middleware->processAttributeValue(
            name: 'foo',
            value: 'bar',
            next: $this->next->fn,
        );

        $this->assertSame('baz', $ret);
    }

    public function testCallsTransformValueForNode(): void
    {
        $this->middleware
            ->shouldReceive('transformValue')
            ->once()
            ->with('foo')
            ->andReturn('bar');

        $this->next
            ->shouldReceive('__invoke')
            ->withAnyArgs()
            ->andReturnArg(0);

        $ret = $this->middleware->processNodeValue(
            value: 'foo',
            next: $this->next->fn,
        );

        $this->assertSame('bar', $ret);
    }
}
