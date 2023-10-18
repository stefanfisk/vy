<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feSpotLight
{
    public static function el(
        mixed $class = null,
        mixed $limitingConeAngle = null,
        mixed $pointsAtX = null,
        mixed $pointsAtY = null,
        mixed $pointsAtZ = null,
        mixed $specularExponent = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feSpotLight',
            props: array_filter(
                [
                    'class' => $class,
                    'limitingConeAngle' => $limitingConeAngle,
                    'pointsAtX' => $pointsAtX,
                    'pointsAtY' => $pointsAtY,
                    'pointsAtZ' => $pointsAtZ,
                    'specularExponent' => $specularExponent,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
