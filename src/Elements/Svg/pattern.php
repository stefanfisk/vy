<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class pattern
{
    public static function el(
        mixed $class = null,
        mixed $patternContentUnits = null,
        mixed $patternTransform = null,
        mixed $patternUnits = null,
        mixed $preserveAspectRatio = null,
        mixed $systemLanguage = null,
        mixed $viewBox = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'pattern',
            props: array_filter(
                [
                    'class' => $class,
                    'patternContentUnits' => $patternContentUnits,
                    'patternTransform' => $patternTransform,
                    'patternUnits' => $patternUnits,
                    'preserveAspectRatio' => $preserveAspectRatio,
                    'systemLanguage' => $systemLanguage,
                    'viewBox' => $viewBox,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
