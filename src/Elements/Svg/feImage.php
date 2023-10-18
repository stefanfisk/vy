<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feImage
{
    public static function el(
        mixed $class = null,
        mixed $preserveAspectRatio = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feImage',
            props: array_filter(
                [
                    'class' => $class,
                    'preserveAspectRatio' => $preserveAspectRatio,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
