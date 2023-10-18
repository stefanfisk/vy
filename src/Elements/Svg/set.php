<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class set
{
    public static function el(
        mixed $class = null,
        mixed $keyPoints = null,
        mixed $repeatCount = null,
        mixed $repeatDur = null,
        mixed $systemLanguage = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'set',
            props: array_filter(
                [
                    'class' => $class,
                    'keyPoints' => $keyPoints,
                    'repeatCount' => $repeatCount,
                    'repeatDur' => $repeatDur,
                    'systemLanguage' => $systemLanguage,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
