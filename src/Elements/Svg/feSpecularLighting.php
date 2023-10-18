<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feSpecularLighting
{
    public static function el(
        mixed $class = null,
        mixed $specularConstant = null,
        mixed $specularExponent = null,
        mixed $surfaceScale = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feSpecularLighting',
            props: array_filter(
                [
                    'class' => $class,
                    'specularConstant' => $specularConstant,
                    'specularExponent' => $specularExponent,
                    'surfaceScale' => $surfaceScale,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
