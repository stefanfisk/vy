<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\rendering;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Rendering\Comparator;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

use function StefanFisk\Vy\el;

#[CoversClass(Comparator::class)]
class ComparatorTest extends TestCase
{
    private function assertValuesAreEqual(mixed $a, mixed $b): void
    {
        $comparator = new Comparator();

        $this->assertTrue($comparator->valuesAreEqual($a, $b));
    }

    private function assertValuesAreNotEqual(mixed $a, mixed $b): void
    {
        $comparator = new Comparator();

        $this->assertFalse($comparator->valuesAreEqual($a, $b));
    }

    public function testEmptyArraysAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [],
            [],
        );
    }

    public function testIntAndFloatAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [1],
            [1.0],
        );
    }

    public function testIntAndStringAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [1],
            ['1'],
        );
    }

    public function testNestedArraysAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [[1]],
            [[1]],
        );
    }

    public function testArraysWithDifferentKeyOrderAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [
                'foo' => 1,
                'bar' => 2,
                'baz' => 3,
            ],
            [
                'baz' => 3,
                'bar' => 2,
                'foo' => 1,
            ],
        );
    }

    public function testArraysOfDifferentLengthAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            [
                'foo' => 1,
                'bar' => 2,
                'baz' => 3,
            ],
        );
        $this->assertValuesAreNotEqual(
            [
                'foo' => 1,
                'bar' => 2,
                'baz' => 3,
            ],
            [
                'foo' => 1,
                'bar' => 2,
            ],
        );
    }

    public function testIdenticalElementsAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [el('div', ['foo' => 'bar'])('baz')],
            [el('div', ['foo' => 'bar'])('baz')],
        );
    }

    public function testElementsWithTheSameKeysAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [el('div', ['key' => '1', 'foo' => 'bar'])('baz')],
            [el('div', ['key' => '1', 'foo' => 'bar'])('baz')],
        );
    }

    public function testElementsWithDifferentKeysAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [el('div', ['key' => '1', 'foo' => 'bar'])('baz')],
            [el('div', ['key' => '2', 'foo' => 'bar'])('baz')],
        );
    }

    public function testElementsOfDifferentTypeAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [el('div', ['foo' => 'bar'])('baz')],
            [el('span', ['foo' => 'bar'])('baz')],
        );
    }

    public function testElementsWithDifferentPropsAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [el('div', ['foo' => 'bar'])('baz')],
            [el('div', ['bar' => 'bar'])('baz')],
        );
    }
}
