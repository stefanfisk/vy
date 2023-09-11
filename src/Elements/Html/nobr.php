<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function StefanFisk\Vy\el;
use function array_filter;

class nobr
{
    public static function el(
        mixed $class = null,
        mixed ...$props,
    ): Element {
        return el('nobr', array_filter([
            'class' => $class,
            ...Utils::mapKeysToKebab($props),
        ]));
    }
}