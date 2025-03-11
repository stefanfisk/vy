<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Element::class)]
class ElementTest extends TestCase
{
    use MocksComponentsTrait;

    /** @param array{key:(string|int|null),type:mixed,props:array<mixed>} $expected */
    private function assertElementEquals(array $expected, Element $actual): void
    {
        $this->assertSame(
            $expected,
            [
                'key' => $actual->key,
                'type' => $actual->type,
                'props' => $actual->props,
            ],
        );
    }

    public function testToChildArrayWrapsSingleItemInArray(): void
    {
        $value = new stdClass();

        $this->assertSame(
            [$value],
            Element::toChildArray($value),
        );
    }

    public function testToChildArrayFlattensArrays(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();
        $value3 = new stdClass();

        $this->assertSame(
            [$value1, $value2, $value3],
            Element::toChildArray([[[$value1, $value2]], $value3]),
        );
    }

    public function testToChildArrayFiltersNullValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, null, $value2]),
        );
    }

    public function testToChildArrayFiltersBoolValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, true, false, $value2]),
        );
    }

    public function testToChildArrayFiltersEmptyStrings(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, '', $value2]),
        );
    }

    public function testInvokeMergesChildrenIntoProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => null,
                'type' => 'div',
                'props' => [
                    'foo' => 'bar',
                    'children' => [['baz', 'qux'], 'quux'],
                ],
            ],
            Element::create('div', [
                'foo' => 'bar',
            ])(
                [
                    'baz',
                    'qux',
                ],
                'quux',
            ),
        );
    }

    public function testInvokeThrowsIfBothChildrenPropAndChildren(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', [
            'foo' => 'bar',
            'children' => 'baz',
        ])(
            'quz',
        );
    }
}
