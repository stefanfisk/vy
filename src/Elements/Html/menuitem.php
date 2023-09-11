<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function StefanFisk\Vy\el;
use function array_filter;

class menuitem
{
    public static function el(
        mixed ...$props,
    ): Element {
        return el('menuitem', array_filter([
            ...Utils::mapKeysToKebab($props),
        ]));
    }
}
