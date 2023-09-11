<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function StefanFisk\Vy\el;
use function array_filter;

class acronym
{
    public static function el(
        mixed ...$props,
    ): Element {
        return el('acronym', array_filter([
            ...Utils::mapKeysToKebab($props),
        ]));
    }
}
