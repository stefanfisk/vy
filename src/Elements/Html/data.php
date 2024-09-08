<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

class data
{
    /**
     * @param array<mixed> $props
     */
    public static function el(array $props = []): Element
    {
        return new Element('data', $props);
    }
}
