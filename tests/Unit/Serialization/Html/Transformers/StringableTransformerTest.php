<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html\Transformers;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Serialization\Html\HtmlableInterface;
use StefanFisk\Vy\Serialization\Html\Transformers\StringableTransformer;
use StefanFisk\Vy\Tests\TestCase;
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

    public function testIgnoresStringableHtmlable(): void
    {
        $value = new class ('<div>My HTML</div>') implements HtmlableInterface, Stringable {
            public function __construct(private readonly string $html)
            {
            }

            public function toHtml(): string
            {
                return $this->html;
            }

            public function __toString(): string
            {
                return $this->html;
            }
        };

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
