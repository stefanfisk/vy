<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class u
{
    /**
     * @param ?non-empty-string $_key
     */
    public static function el(
        mixed $class = null,
        ?string $_key = null,
        mixed ...$props,
    ): Element {
        if ($class !== null) {
            $props['class'] = $class;
        }

        return new Element(
            key: $_key,
            type: 'u',
            props: $props,
        );
    }
}
