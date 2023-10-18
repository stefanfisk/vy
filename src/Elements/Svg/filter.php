<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class filter
{
    public static function el(
        mixed $class = null,
        mixed $filterUnits = null,
        mixed $primitiveUnits = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'filter',
            props: array_filter(
                [
                    'class' => $class,
                    'filterUnits' => $filterUnits,
                    'primitiveUnits' => $primitiveUnits,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
