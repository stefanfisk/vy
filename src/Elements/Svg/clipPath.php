<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

final class clipPath
{
    /**
     * @param array<non-empty-string,mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('clipPath', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('clipPath', [
            'class' => $class,
        ]);
    }
}
