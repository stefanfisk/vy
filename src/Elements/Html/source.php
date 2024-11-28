<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\VoidElement;

class source
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
            type: 'source',
            props: $props,
        );
    }
}
