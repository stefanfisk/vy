<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class text
{
    public static function el(
        mixed $class = null,
        mixed $lengthAdjust = null,
        mixed $systemLanguage = null,
        mixed $textLength = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'text',
            props: array_filter(
                [
                    'class' => $class,
                    'lengthAdjust' => $lengthAdjust,
                    'systemLanguage' => $systemLanguage,
                    'textLength' => $textLength,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
