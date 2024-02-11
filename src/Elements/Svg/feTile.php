<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

class feTile
{
    /**
     * @param non-empty-string|null $_key
     */
    public static function el(
        mixed $class = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        if ($class !== null) {
            $props['class'] = $class;
        }

        return new Element(
            key: $_key,
            type: 'feTile',
            props: $props,
        );
    }
}
