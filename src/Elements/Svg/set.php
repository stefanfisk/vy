<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

final class set
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('set', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('set', [
            'class' => $class,
        ]);
    }
}
