<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support;

use StefanFisk\Vy\Element;

class BarComponent
{
    public static function el(string $foo): Element
    {
        return Element::create(self::render(...), [
            'foo' => $foo,
        ]);
    }

    private static function render(string $foo, mixed $children): mixed
    {
        return Element::create('div', [
            'data-foo' => $foo,
        ])(
            Element::create('div', [
                'class' => 'children',
            ])(
                $children,
            ),
        );
    }

    private function __construct()
    {
    }
}
