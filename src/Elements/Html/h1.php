<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

class h1
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
            type: 'h1',
            props: $props,
        );
    }
}
