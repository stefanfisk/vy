<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feGaussianBlur
{
    public static function el(
        mixed $class = null,
        mixed $edgeMode = null,
        mixed $stdDeviation = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feGaussianBlur',
            props: array_filter(
                [
                    'class' => $class,
                    'edgeMode' => $edgeMode,
                    'stdDeviation' => $stdDeviation,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
