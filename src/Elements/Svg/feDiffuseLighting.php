<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feDiffuseLighting
{
    public static function el(
        mixed $class = null,
        mixed $diffuseConstant = null,
        mixed $surfaceScale = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feDiffuseLighting',
            props: array_filter(
                [
                    'class' => $class,
                    'diffuseConstant' => $diffuseConstant,
                    'surfaceScale' => $surfaceScale,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
