<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

final class title
{
    /**
     * @param array<non-empty-string,mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('title', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('title', [
            'class' => $class,
        ]);
    }
}
