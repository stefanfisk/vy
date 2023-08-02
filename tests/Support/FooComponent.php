<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support;

use StefanFisk\PhpReact\Element;

use function StefanFisk\PhpReact\el;

class FooComponent
{
    public static function el(string $foo, mixed $children = null): Element
    {
        return el(self::class, [
            'foo' => $foo,
            'children' => $children,
        ]);
    }

    public function render(string $foo, mixed $children): mixed
    {
        return el('div', [
            'data-foo' => $foo,
        ], [
            el('div', [
                'class' => 'children',
            ], [
                $children,
            ]),
        ]);
    }
}
