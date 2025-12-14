<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

final class a
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('a', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('a', [
            'class' => $class,
        ]);
    }
}
