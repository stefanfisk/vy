<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Transformers;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Serialization\Html\Transformers\StringableTransformer;
use StefanFisk\PhpReact\Tests\TestCase;
use Stringable;
use stdClass;

#[CoversClass(StringableTransformer::class)]
class StringableTransformerTest extends TestCase
{
    private StringableTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new StringableTransformer();
    }

    public function testIgnoresNonStringables(): void
    {
        $value = new stdClass();

        $this->assertSame(
            $value,
            $this->transformer->transformValue($value),
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
            $this->transformer->transformValue($value),
        );
    }
}
