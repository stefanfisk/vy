<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Components\Fragment;
use StefanFisk\PhpReact\Element;
use stdClass;

/**
 * @covers StefanFisk\PhpReact\Element
 */
class ElementTest extends TestCase
{
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

    public function testCreateTakesStringKeyFromProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => 'baz',
                'type' => 'div',
                'props' => [],
            ],
            Element::create('div', ['key' => 'baz']),
        );
    }

    public function testCreateTakesIntKeyFromProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => '123',
                'type' => 'div',
                'props' => [],
            ],
            Element::create('div', ['key' => 123]),
        );
    }

    public function testCreateRemovesKeyFromProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => 'baz',
                'type' => 'div',
                'props' => ['foo' => 'bar'],
            ],
            Element::create('div', [
                'key' => 'baz',
                'foo' => 'bar',
            ]),
        );
    }

    public function testCreateThrowsIfKeyIsWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', ['key' => ['foo' => 'bar']]);
    }

    public function testCreateThrowsIfKeyIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', ['key' => '']);
    }

    public function testCreateMergesChildrenIntoProps(): void
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
            Element::create('div', ['foo' => 'bar'], ['baz', 'qux'], 'quux'),
        );
    }

    public function testCreateDoesNotMergeEmptyChildrenIntoProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => null,
                'type' => 'div',
                'props' => ['foo' => 'bar'],
            ],
            Element::create('div', ['foo' => 'bar']),
        );
    }

    public function testCreatePassesChildrenPropAsIs(): void
    {
        $this->assertElementEquals(
            [
                'key' => null,
                'type' => 'div',
                'props' => ['foo' => 'bar', 'children' => 'baz'],
            ],
            Element::create('div', ['foo' => 'bar', 'children' => 'baz']),
        );
    }

    public function testCreateThrowsIfBothChildrenPropAndChildren(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', ['foo' => 'bar', 'children' => 'baz'], ['quz']);
    }

    public function testCreateConvertsEmptyStringTypeToFragments(): void
    {
        $this->assertElementEquals(
            [
                'key' => null,
                'type' => Fragment::class,
                'props' => ['foo' => 'bar', 'children' => 'baz'],
            ],
            Element::create('', ['foo' => 'bar', 'children' => 'baz']),
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
}
