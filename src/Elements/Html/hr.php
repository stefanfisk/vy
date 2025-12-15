<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class hr
{
    /**
     * @param array<non-empty-string,mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('hr', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('hr', [
            'class' => $class,
        ]);
    }
}
