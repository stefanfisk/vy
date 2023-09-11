<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function StefanFisk\Vy\el;
use function array_filter;

class h1
{
    public static function el(
        mixed ...$props,
    ): Element {
        return el('h1', array_filter([
            ...Utils::mapKeysToKebab($props),
        ]));
    }
}
