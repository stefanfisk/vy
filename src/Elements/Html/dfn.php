<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class dfn
{
    /**
     * @param array<mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('dfn', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('dfn', [
            'class' => $class,
        ]);
    }
}
