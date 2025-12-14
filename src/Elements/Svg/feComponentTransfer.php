<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

final class feComponentTransfer
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('feComponentTransfer', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('feComponentTransfer', [
            'class' => $class,
        ]);
    }
}
