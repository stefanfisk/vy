<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversFunction;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Tests\TestCase;

use function StefanFisk\Vy\el;

#[CoversFunction('StefanFisk\\Vy\\el')]
class ElTest extends TestCase
{
    /** @param array{type:mixed,props:array<mixed>} $expected */
    private function assertElementEquals(array $expected, Element $actual): void
    {
        $this->assertSame(
            $expected,
            [
                'type' => $actual->type,
                'props' => $actual->props,
            ],
        );
    }

    public function testDoesNotMergeEmptyChildrenIntoProps(): void
    {
        $this->assertElementEquals(
            [
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
                'type' => 'div',
                'props' => ['foo' => 'bar', 'children' => 'baz'],
            ],
            el('div', ['foo' => 'bar', 'children' => 'baz']),
        );
    }
}
