<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html\Transformers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Serialization\Html\Transformers\ClassAttributeTransformer;
use StefanFisk\Vy\Tests\TestCase;
use Throwable;
use stdClass;

#[CoversClass(ClassAttributeTransformer::class)]
class ClassAttributeTransformerTest extends TestCase
{
    /**
     * @param array<non-empty-string,mixed> $expected
     * @param array<non-empty-string,mixed> $attributes
     */
    private static function assertAttributesEquals(array $expected, array $attributes): void
    {
        $transformer = new ClassAttributeTransformer();

        self::assertSame($expected, $transformer->processAttributes($attributes));
    }

   /**
     * @param class-string<Throwable> $exception
     * @param array<non-empty-string,mixed> $attributes
     */
    private function assertThrowsForAttributes(string $exception, array $attributes): void
    {
        $transformer = new ClassAttributeTransformer();

        $this->expectException($exception);

        $transformer->processAttributes($attributes);
    }

    /**
     * @param ?non-empty-string $expected
     */
    private static function assertClassEquals(?string $expected, mixed $value): void
    {
        if ($expected !== null) {
            $expected = ['class' => $expected];
        } else {
            $expected = [];
        }

        self::assertAttributesEquals($expected, ['class' => $value]);
    }

    /**
     * @param class-string<Throwable> $exception
     */
    private function assertThrowsForClass(string $exception, mixed $value): void
    {
        $this->assertThrowsForAttributes($exception, ['class' => $value]);
    }

    public function testIgnoresNonClassAttributes(): void
    {
        $value = new stdClass();

        self::assertAttributesEquals(['foo' => $value], ['foo' => $value]);
    }

    public function testThrowsForObjectValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->assertThrowsForClass(InvalidArgumentException::class, new stdClass());
    }

    public function testThrowsForObjectSubvalue(): void
    {
        $this->assertThrowsForClass(InvalidArgumentException::class, [new stdClass()]);
    }

    public function testNull(): void
    {
        $this->assertClassEquals(null, null);
    }

    public function testTrue(): void
    {
        $this->assertClassEquals(null, true);
    }

    public function testFalse(): void
    {
        $this->assertClassEquals(null, false);
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
            'baz foo',
            [
                'foo',
                'bar' => false,
                'baz' => true,
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
