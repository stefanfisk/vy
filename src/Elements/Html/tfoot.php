<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function StefanFisk\Vy\el;
use function array_filter;

class tfoot
{
    public static function el(
        mixed ...$props,
    ): Element {
        return el('tfoot', array_filter([
            ...Utils::mapKeysToKebab($props),
        ]));
    }
}
