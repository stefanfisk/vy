<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

class track
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
            type: 'track',
            props: $props,
        );
    }
}
