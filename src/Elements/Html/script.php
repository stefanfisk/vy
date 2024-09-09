<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\RawTextElement;

class script
{
    /**
     * @param ?non-empty-string $_key
     */
    public static function el(
        mixed $class = null,
        ?string $_key = null,
        mixed ...$props,
    ): RawTextElement {
        if ($class !== null) {
            $props['class'] = $class;
        }

        return new RawTextElement(
            key: $_key,
            type: 'script',
            props: $props,
        );
    }
}
