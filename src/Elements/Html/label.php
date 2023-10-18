<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Html;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class label
{
    public static function el(
        mixed $class = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'label',
            props: array_filter(
                [
                    'class' => $class,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
