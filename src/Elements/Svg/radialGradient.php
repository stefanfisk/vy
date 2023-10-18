<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class radialGradient
{
    public static function el(
        mixed $class = null,
        mixed $spreadMethod = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'radialGradient',
            props: array_filter(
                [
                    'class' => $class,
                    'spreadMethod' => $spreadMethod,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
