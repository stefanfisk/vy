<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;

final class strong
{
    /**
     * @param array<non-empty-string,mixed> $attrs
     */
    public static function el(array $attrs = []): Element
    {
        return new Element('strong', $attrs);
    }

    public static function cx(mixed $class): Element
    {
        return new Element('strong', [
            'class' => $class,
        ]);
    }
}
