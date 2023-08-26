<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html\Transformers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Serialization\Html\Transformers\StyleAttributeTransformer;
use StefanFisk\Vy\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\Vy\Tests\TestCase;
use Throwable;
use stdClass;

#[CoversClass(StyleAttributeTransformer::class)]
class StyleAttributeTransformerTest extends TestCase
{
    use MocksInvokablesTrait;

    private StyleAttributeTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new StyleAttributeTransformer();
    }

    private function assertStyleEquals(string $expected, mixed $value): void
    {
        $this->assertSame(
            $expected,
            $this->transformer->processAttributeValue(
                name: 'style',
                value: $value,
            ),
        );
    }

    /** @param class-string<Throwable> $exception */
    private function assertThrowsForStyle(string $exception, mixed $value): void
    {
        $this->expectException($exception);

        $this->transformer->processAttributeValue(
            name: 'style',
            value: $value,
        );
    }

    public function testIgnoresNonStyleAttributes(): void
    {
        $value = new stdClass();

        $this->assertSame(
            $value,
            $this->transformer->processAttributeValue(
                name: 'foo',
                value: $value,
            ),
        );
    }

    public function testThrowsForObjectValue(): void
    {
        $this->assertThrowsForStyle(InvalidArgumentException::class, new stdClass());
    }

    public function testThrowsForObjectSubValue(): void
    {
        $this->assertThrowsForStyle(InvalidArgumentException::class, [new stdClass()]);
    }

    public function testNull(): void
    {
        $this->assertStyleEquals('', null);
    }

    public function testEmptyString(): void
    {
        $this->assertStyleEquals('', '');
    }

    public function testString(): void
    {
        $this->assertStyleEquals('foo:bar', 'foo:bar');
    }

    public function testIndexedString(): void
    {
        $this->assertStyleEquals('foo:bar', ['foo:bar']);
    }

    public function testKeyedString(): void
    {
        $this->assertStyleEquals('foo:bar', ['foo' => 'bar']);
    }

    public function testNestedStrings(): void
    {
        $this->assertStyleEquals('foo:bar;baz:qux', [
            ['foo' => 'bar'],
            ['baz' => 'qux'],
        ]);
    }
}
