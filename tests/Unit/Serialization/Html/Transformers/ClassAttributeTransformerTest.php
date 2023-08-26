<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html\Transformers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Serialization\Html\Transformers\ClassAttributeTransformer;
use StefanFisk\Vy\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(ClassAttributeTransformer::class)]
class ClassAttributeTransformerTest extends TestCase
{
    use MocksInvokablesTrait;

    private ClassAttributeTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ClassAttributeTransformer();
    }

    private function assertClassEquals(string | null $expected, mixed $value): void
    {
        $this->assertSame(
            $expected,
            $this->transformer->processAttributeValue(
                name: 'class',
                value: $value,
            ),
        );
    }

    public function testIgnoresNonClassAttributes(): void
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
        $this->expectException(InvalidArgumentException::class);

        $this->transformer->processAttributeValue(
            name: 'class',
            value: new stdClass(),
        );
    }

    public function testThrowsForObjectSubvalue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->transformer->processAttributeValue(
            name: 'class',
            value: [new stdClass()],
        );
    }

    public function testNull(): void
    {
        $this->assertClassEquals(null, null);
    }

    public function testEmptyString(): void
    {
        $this->assertClassEquals(null, '');
    }

    public function testSortsString(): void
    {
        $this->assertClassEquals('bar foo', 'foo bar');
    }

    public function testConditional(): void
    {
        $this->assertClassEquals(
            'bar foo',
            [
                'foo',
                'bar' => true,
                'baz' => false,
            ],
        );
    }

    public function testNestedConditional(): void
    {
        $this->assertClassEquals(
            'bar foo',
            [
                'foo',
                ['bar' => true],
                ['baz' => false],
            ],
        );
    }

    public function testSortsConditional(): void
    {
        $this->assertClassEquals(
            'bar baz foo',
            [
                'foo bar' => true,
                'baz',
            ],
        );
    }

    public function testConflictingConditionals(): void
    {
        $this->assertClassEquals(
            'foo',
            [
                ['foo' => true],
                ['foo' => false],
            ],
        );
    }
}
