<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;

class path
{
    /**
     * @param array<mixed> $props
     */
    public static function el(array $props = []): Element
    {
        return new Element('path', $props);
    }
}
