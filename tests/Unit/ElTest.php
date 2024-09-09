<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversFunction;
use StefanFisk\Vy\BaseElement;
use StefanFisk\Vy\Tests\TestCase;

use function StefanFisk\Vy\el;

#[CoversFunction('StefanFisk\\Vy\\el')]
class ElTest extends TestCase
{
    /** @param array{key:(string|int|null),type:mixed,props:array<mixed>} $expected */
    private function assertElementEquals(array $expected, BaseElement $actual): void
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
            el('div', ['key' => 'baz']),
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
            el('div', ['key' => 123]),
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
            el('div', [
                'key' => 'baz',
                'foo' => 'bar',
            ]),
        );
    }

    public function testThrowsIfKeyIsWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        el('div', ['key' => ['foo' => 'bar']]);
    }

    public function testThrowsIfKeyIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        el('div', ['key' => '']);
    }

    public function testDoesNotMergeEmptyChildrenIntoProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => null,
                'type' => 'div',
                'props' => ['foo' => 'bar'],
            ],
            el('div', ['foo' => 'bar']),
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
            el('div', ['foo' => 'bar', 'children' => 'baz']),
        );
    }
}
