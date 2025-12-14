<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class figure
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('figure', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('figure', [
            'class' => $class,
        ]);
    }
}
