<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StringableMiddleware;
use StefanFisk\PhpReact\Tests\TestCase;
use Stringable;
use stdClass;

#[CoversClass(StringableMiddleware::class)]
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
        $value = $this->mockery(Stringable::class);
        $value
            ->shouldReceive('__toString')
            ->with()
            ->once()
            ->andReturn('foo');

        $this->assertSame(
            'foo',
            $this->middleware->transformValue($value),
        );
    }
}
