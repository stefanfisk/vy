<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class foreignObject
{
    public static function el(
        mixed $class = null,
        mixed $systemLanguage = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'foreignObject',
            props: array_filter(
                [
                    'class' => $class,
                    'systemLanguage' => $systemLanguage,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
