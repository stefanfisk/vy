<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\EscapableRawTextElement;

final class title
{
    /**
     * @param ?non-empty-string $_key
     */
    public static function el(
        mixed $class = null,
        ?string $_key = null,
        mixed ...$props,
    ): EscapableRawTextElement {
        if ($class !== null) {
            $props['class'] = $class;
        }

        return new EscapableRawTextElement(
            key: $_key,
            type: 'title',
            props: $props,
        );
    }
}
