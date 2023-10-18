<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements\Svg;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Utils;

use function array_filter;

class feConvolveMatrix
{
    public static function el(
        mixed $class = null,
        mixed $edgeMode = null,
        mixed $kernelMatrix = null,
        mixed $preserveAlpha = null,
        mixed $targetX = null,
        mixed $targetY = null,
        string | null $_key = null,
        mixed ...$props,
    ): Element {
        return new Element(
            key: $_key,
            type: 'feConvolveMatrix',
            props: array_filter(
                [
                    'class' => $class,
                    'edgeMode' => $edgeMode,
                    'kernelMatrix' => $kernelMatrix,
                    'preserveAlpha' => $preserveAlpha,
                    'targetX' => $targetX,
                    'targetY' => $targetY,
                    ...Utils::mapArgsToAtts($props),
                ],
                fn ($value) => $value !== null,
            ),
        );
    }
}
