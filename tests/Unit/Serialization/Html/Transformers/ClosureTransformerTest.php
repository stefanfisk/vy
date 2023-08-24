<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Transformers;

use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use StefanFisk\PhpReact\Serialization\Html\Transformers\ClosureTransformer;
use StefanFisk\PhpReact\Tests\TestCase;
use Throwable;
use UnexpectedValueException;
use stdClass;

use function ob_get_level;
use function ob_start;

#[CoversClass(ClosureTransformer::class)]
class ClosureTransformerTest extends TestCase
{
    private ClosureTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ClosureTransformer();
    }

    public function testIgnoresNonClosures(): void
    {
        $value = new stdClass();

        $this->assertSame(
            $value,
            $this->transformer->transformValue($value),
        );
    }

    public function testThrowsIfClosureBothOutputsAndReturns(): void
    {
        $value = function () {
            echo 'foo';

            return 'bar';
        };

        $this->expectException(UnexpectedValueException::class);

        $this->transformer->transformValue($value);
    }

    public function testReturnsOutput(): void
    {
        $value = function () {
            echo 'foo';
        };

        $this->assertSame(
            'foo',
            $this->transformer->transformValue($value),
        );
    }

    public function testReturnsReturn(): void
    {
        $value = fn () => 'foo';

        $this->assertSame(
            'foo',
            $this->transformer->transformValue($value),
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
            $this->transformer->transformValue($value);
        } catch (Throwable $e) {
        }

        $this->assertSame($obLevel, ob_get_level());
    }
}
