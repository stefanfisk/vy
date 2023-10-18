<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feTurbulence
{
    public static function el(
        mixed $class = null,
        mixed $baseFrequency = null,
        mixed $numOctaves = null,
        mixed $stitchTiles = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feTurbulence',
            props: array_filter(
                [
                    'class' => $class,
                    'baseFrequency' => $baseFrequency,
                    'numOctaves' => $numOctaves,
                    'stitchTiles' => $stitchTiles,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
