<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StringableMiddleware;
use Stringable;
use stdClass;

/**
 * @covers StefanFisk\PhpReact\Serialization\Html\Middleware\StringableMiddleware
 */
class StringableMiddlewareTest extends TestCase
{
    private StringableMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new StringableMiddleware();
    }

    public function testIgnoresNonStringables(): void
    {
        $value = new stdClass();

        $this->assertSame(
            $value,
            $this->middleware->transformValue($value),
        );
    }

    public function testCallsToStringOnStringables(): void
    {
        $value = $this->createMock(Stringable::class);
        $value->expects($this->once())
            ->method('__toString')
            ->with()
            ->willReturn('foo');

        $this->assertSame(
            'foo',
            $this->middleware->transformValue($value),
        );
    }
}
