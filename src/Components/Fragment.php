<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Components;

use StefanFisk\Vy\Element;

final class Fragment
{
    public static function el(): Element
    {
        return new Element(type: self::class);
    }

    public function render(mixed $children = null): mixed
    {
        return $children;
    }
}
