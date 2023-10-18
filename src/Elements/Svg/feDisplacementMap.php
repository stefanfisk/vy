<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feDisplacementMap
{
    public static function el(
        mixed $class = null,
        mixed $xChannelSelector = null,
        mixed $yChannelSelector = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feDisplacementMap',
            props: array_filter(
                [
                    'class' => $class,
                    'xChannelSelector' => $xChannelSelector,
                    'yChannelSelector' => $yChannelSelector,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
