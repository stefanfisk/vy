<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Examples\ArticleCardGrid;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Html\div;

class CardGrid
{
    public static function el(): Element
    {
        return Element::create(self::render(...));
    }

    private static function render(mixed $children = null): mixed
    {
        return div::cx([
            'grid',
            'gap-8',
            'md:grid-cols-3',
        ])(
            $children,
        );
    }
}
