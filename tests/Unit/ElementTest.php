<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Element;

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

    public function testTakesStringKeyFromProps(): void
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

    public function testTakesIntKeyFromProps(): void
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

    public function testRemovesKeyFromProps(): void
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

    public function testThrowsIfKeyIsWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', ['key' => ['foo' => 'bar']]);
    }

    public function testThrowsIfKeyIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', ['key' => '']);
    }

    public function testMergesChildrenIntoProps(): void
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

    public function testDoesNotMergeEmptyChildrenIntoProps(): void
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

    public function testPassesChildrenPropAsIs(): void
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

    public function testThrowsIfBothChildrenPropAndChildren(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', ['foo' => 'bar', 'children' => 'baz'], ['quz']);
    }
}
