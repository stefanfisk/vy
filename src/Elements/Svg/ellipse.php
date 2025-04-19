<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\VoidElement;

final class ellipse
{
    /**
     * @param ?non-empty-string $_key
     */
    public static function el(
        mixed $class = null,
        ?string $_key = null,
        mixed ...$props,
    ): VoidElement {
        if ($class !== null) {
            $props['class'] = $class;
        }

        return new VoidElement(
            key: $_key,
            type: 'ellipse',
            props: $props,
        );
    }
}
