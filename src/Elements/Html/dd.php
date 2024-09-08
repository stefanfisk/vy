<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

class dd
{
    /**
     * @param array<mixed> $props
     */
    public static function el(array $props = []): Element
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new Element('dd', $props);
    }
}
