<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function StefanFisk\Vy\el;
use function array_filter;

class svg
{
    public static function el(
        mixed $class = null,
        mixed $viewBox = null,
        mixed ...$props,
    ): Element {
        return el('svg', array_filter([
            'class' => $class,
            'viewBox' => $viewBox,
            ...Utils::mapKeysToKebab($props),
        ]));
    }
}
