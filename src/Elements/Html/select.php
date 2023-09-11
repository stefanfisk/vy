<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Html\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function StefanFisk\Vy\el;
use function array_filter;

class select
{
    public static function el(
        mixed ...$props,
    ): Element {
        return el('select', array_filter([
            ...Utils::mapKeysToKebab($props),
        ]));
    }
}
