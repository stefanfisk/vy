<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Support\PropsComparator;

use function StefanFisk\PhpReact\el;

#[CoversClass(PropsComparator::class)]
class PropsComparatorTest extends TestCase
{
    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function assertPropsAreEqual(array $a, array $b): void
    {
        $comparator = new PropsComparator();

        $this->assertTrue($comparator->propsAreEqual($a, $b));
    }

    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function assertPropsAreNotEqual(array $a, array $b): void
    {
        $comparator = new PropsComparator();

        $this->assertFalse($comparator->propsAreEqual($a, $b));
    }

    public function testEmptyArraysAreEqual(): void
    {
        $this->assertPropsAreEqual(
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
        $this->assertPropsAreEqual(
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
        $this->assertPropsAreEqual(
            [el('div', ['foo' => 'bar'], 'baz')],
            [el('div', ['foo' => 'bar'], 'baz')],
        );
    }

    public function testElementsOfDifferentTypeAreNotEqual(): void
    {
        $this->assertPropsAreNotEqual(
            [el('div', ['foo' => 'bar'], 'baz')],
            [el('span', ['foo' => 'bar'], 'baz')],
        );
    }

    public function testElementsWithDifferentPropsAreNotEqual(): void
    {
        $this->assertPropsAreNotEqual(
            [el('div', ['foo' => 'bar'], 'baz')],
            [el('div', ['bar' => 'bar'], 'baz')],
        );
    }
}
