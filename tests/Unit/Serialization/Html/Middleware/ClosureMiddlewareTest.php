<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use StefanFisk\PhpReact\Serialization\Html\Middleware\ClosureMiddleware;
use Throwable;
use UnexpectedValueException;
use stdClass;

use function ob_get_level;
use function ob_start;

/**
 * @covers StefanFisk\PhpReact\Serialization\Html\Middleware\ClosureMiddleware
 */
class ClosureMiddlewareTest extends TestCase
{
    private ClosureMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new ClosureMiddleware();
    }

    public function testIgnoresNonClosures(): void
    {
        $value = new stdClass();

        $this->assertSame(
            $value,
            $this->middleware->transformValue($value),
        );
    }

    public function testThrowsIfClosureBothOutputsAndReturns(): void
    {
        $value = function () {
            echo 'foo';

            return 'bar';
        };

        $this->expectException(UnexpectedValueException::class);

        $this->middleware->transformValue($value);
    }

    public function testReturnsOutput(): void
    {
        $value = function () {
            echo 'foo';
        };

        $this->assertSame(
            'foo',
            $this->middleware->transformValue($value),
        );
    }

    public function testReturnsReturn(): void
    {
        $value = fn () => 'foo';

        $this->assertSame(
            'foo',
            $this->middleware->transformValue($value),
        );
    }

    public function testClosesOutputBuffersOnCatch(): void
    {
        $value = function () {
            ob_start();

            throw new RuntimeException();
        };

        $obLevel = ob_get_level();

        try {
            $this->middleware->transformValue($value);
        } catch (Throwable $e) {
        }

        $this->assertSame($obLevel, ob_get_level());
    }
}
