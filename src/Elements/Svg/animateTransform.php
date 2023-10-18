<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class animateTransform
{
    public static function el(
        mixed $class = null,
        mixed $calcMode = null,
        mixed $keyPoints = null,
        mixed $keySplines = null,
        mixed $keyTimes = null,
        mixed $repeatCount = null,
        mixed $repeatDur = null,
        mixed $systemLanguage = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'animateTransform',
            props: array_filter(
                [
                    'class' => $class,
                    'calcMode' => $calcMode,
                    'keyPoints' => $keyPoints,
                    'keySplines' => $keySplines,
                    'keyTimes' => $keyTimes,
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
