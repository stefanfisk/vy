<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

final class textPath
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
            type: 'textPath',
            props: $props,
        );
    }
}
