<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

use InvalidArgumentException;
use Override;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_walk_recursive;
use function get_debug_type;
use function implode;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

final class StyleAttributeTransformer implements AttributesTransformerInterface
{
    /** {@inheritDoc} */
    #[Override]
    public function processAttributes(array $attributes): array
    {
        if (!array_key_exists('style', $attributes)) {
            return $attributes;
        }

        $styleAttr = $attributes['style'];

        if (!$styleAttr) {
            unset($attributes['style']);

            return $attributes;
        }

        if (is_string($styleAttr)) {
            return $attributes;
        }

        if (! is_array($styleAttr)) {
            throw new InvalidArgumentException(sprintf('Unsupported type `%s`.', get_debug_type($styleAttr)));
        }

        // We wrap $effectiveStyles to make psalm happy.
        $wrapper = new class {
            /** @var array<string> */
            public array $effectiveStyles = [];
        };

        array_walk_recursive(
            $styleAttr,
            function (mixed $value, int | string $key) use ($wrapper): void {
                if ($value === null || $value === '' || $value === false || $value === true) {
                    return;
                }

                if (is_int($value) || is_float($value)) {
                    $value = (string) $value;
                }

                if (!is_string($value)) {
                    throw new InvalidArgumentException(sprintf('Unsupported type `%s`.', get_debug_type($value)));
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

        if ($effectiveStyles === '') {
            unset($attributes['style']);

            return $attributes;
        }

        $attributes['style'] = $effectiveStyles;

        return $attributes;
    }
}
