<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

use InvalidArgumentException;
use Override;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function explode;
use function get_debug_type;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function sort;
use function sprintf;

final class ClassAttributeTransformer implements AttributesTransformerInterface
{
    /** {@inheritDoc} */
    #[Override]
    public function processAttributes(array $attributes): array
    {
        if (!array_key_exists('class', $attributes)) {
            return $attributes;
        }

        $oldValue = $attributes['class'];

        /** @var array<string,true> $classToTrue */
        $classToTrue = [];

        $this->walk($oldValue, $classToTrue);

        if ($classToTrue === []) {
            unset($attributes['class']);

            return $attributes;
        }

        $classes = array_keys($classToTrue);

        sort($classes);

        $newValue = implode(' ', $classes);

        $attributes['class'] = $newValue;

        return $attributes;
    }

    /** @param array<string,true> &$effectiveClasses */
    private function walk(mixed $class, array &$effectiveClasses): void
    {
        if (!$class || $class === true) {
            return;
        } elseif (is_string($class)) {
            $classes = array_filter(explode(' ', $class));

            foreach ($classes as $class) {
                $effectiveClasses[$class] = true;
            }
        } elseif (is_array($class)) {
            foreach ($class as $key => $value) {
                if (is_int($key)) {
                    $conditional = true;
                    $class = $value;
                } else {
                    $conditional = $value;
                    $class = $key;
                }

                if (!(bool) $conditional || !(bool) $class) {
                    continue;
                }

                $this->walk($class, $effectiveClasses);
            }
        } else {
            throw new InvalidArgumentException(sprintf('Unsupported type `%s`.', get_debug_type($class)));
        }
    }
}
