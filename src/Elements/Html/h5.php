<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class h5
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('h5', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('h5', [
            'class' => $class,
        ]);
    }
}
