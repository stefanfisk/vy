<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

final class feDistantLight
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('feDistantLight', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('feDistantLight', [
            'class' => $class,
        ]);
    }
}
