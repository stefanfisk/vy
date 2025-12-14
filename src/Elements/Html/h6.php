<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class h6
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('h6', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('h6', [
            'class' => $class,
        ]);
    }
}
