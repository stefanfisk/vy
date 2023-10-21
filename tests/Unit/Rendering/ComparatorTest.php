<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\rendering;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Rendering\Comparator;
use StefanFisk\Vy\Tests\TestCase;

use function StefanFisk\Vy\el;

#[CoversClass(Comparator::class)]
class ComparatorTest extends TestCase
{
    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function assertValuesAreEqual(array $a, array $b): void
    {
        $comparator = new Comparator();

        $this->assertTrue($comparator->valuesAreEqual($a, $b));
    }

    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function assertPropsAreNotEqual(array $a, array $b): void
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
        $this->assertPropsAreNotEqual(
            [1],
            [1.0],
        );
    }

    public function testIntAndStringAreNotEqual(): void
    {
        $this->assertPropsAreNotEqual(
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
        $this->assertPropsAreNotEqual(
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
        $this->assertPropsAreNotEqual(
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
        $this->assertPropsAreNotEqual(
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

    public function testElementsOfDifferentTypeAreNotEqual(): void
    {
        $this->assertPropsAreNotEqual(
            [el('div', ['foo' => 'bar'])('baz')],
            [el('span', ['foo' => 'bar'])('baz')],
        );
    }

    public function testElementsWithDifferentPropsAreNotEqual(): void
    {
        $this->assertPropsAreNotEqual(
            [el('div', ['foo' => 'bar'])('baz')],
            [el('div', ['bar' => 'bar'])('baz')],
        );
    }
}
