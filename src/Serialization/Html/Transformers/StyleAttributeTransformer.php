<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

use InvalidArgumentException;

use function array_filter;
use function array_map;
use function array_walk_recursive;
use function gettype;
use function implode;
use function is_array;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

class StyleAttributeTransformer implements AttributeValueTransformerInterface
{
    /** {@inheritDoc} */
    public function processAttributeValue(string $name, mixed $value): mixed
    {
        if ($name !== 'style') {
            return $value;
        }

        $value = $this->apply($value);

        return $value;
    }

    private function apply(mixed $styles): string
    {
        if (! $styles) {
            return '';
        }

        if (is_string($styles)) {
            return $styles;
        }

        if (! is_array($styles)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported type `%s`.',
                is_object($styles) ? $styles::class : gettype($styles),
            ));
        }

        // We wrap $effectiveStyles to make psalm happy.
        $wrapper = new class {
            /** @var array<string> */
            public array $effectiveStyles = [];
        };

        array_walk_recursive(
            $styles,
            function (mixed $value, int | string $key) use ($wrapper): void {
                if (!$value || $value === true) {
                    return;
                }

                if (is_int($value) || is_float($value)) {
                    $value = (string) $value;
                }

                if (!is_string($value)) {
                    throw new InvalidArgumentException(sprintf(
                        'Unsupported type `%s`.',
                        is_object($value) ? $value::class : gettype($value),
                    ));
                }

                if (is_int($key)) {
                    $style = $value;
                } else {
                    $style = "$key:$value";
                }

                $wrapper->effectiveStyles[] = $style;
            },
        );

        $effectiveStyles = array_map('trim', $wrapper->effectiveStyles);
        $effectiveStyles = array_filter($effectiveStyles);
        $effectiveStyles = implode(';', $effectiveStyles);

        return $effectiveStyles;
    }
}
