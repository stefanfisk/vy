<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class view
{
    public static function el(
        mixed $class = null,
        mixed $preserveAspectRatio = null,
        mixed $viewBox = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'view',
            props: array_filter(
                [
                    'class' => $class,
                    'preserveAspectRatio' => $preserveAspectRatio,
                    'viewBox' => $viewBox,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
