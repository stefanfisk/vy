<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class section
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('section', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('section', [
            'class' => $class,
        ]);
    }
}
