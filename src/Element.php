<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;
use StefanFisk\Vy\Components\Fragment;

use function array_merge;
use function array_reduce;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;

class Element
{
    /**
     * @param string|Closure|object|class-string $type
     * @param array<mixed> $props
     */
    public static function create(mixed $type, array $props = [], mixed ...$children): Element
    {
        // Key

        $key = $props['key'] ?? null;
        unset($props['key']);

        if ($key !== null) {
            if (is_string($key)) {
                if ($key === '') {
                    throw new InvalidArgumentException('"key" cannot be an empty string.');
                }
            } elseif (is_int($key)) {
                $key = (string) $key;
            } else {
                throw new InvalidArgumentException('"key" must be null, string or numeric.');
            }
        }

        // Type

        if ($type === '') {
            $type = Fragment::class;
        }

        // Children

        if ($children) {
            if ($props['children'] ?? null) {
                throw new InvalidArgumentException('Both $props[children] and $children are non-empty.');
            }

            $props['children'] = $children;
        }

        // Create

        return new Element($key, $type, $props);
    }

    /** @return list<mixed> */
    public static function toChildArray(mixed $renderChildren): array
    {
        // Flatten the render children and remove empty items

        if (! is_array($renderChildren)) {
            $renderChildren = [$renderChildren];
        }

        return array_reduce(
            $renderChildren,
            /** @param list<mixed> $carry */
            function (array $carry, mixed $el) {
                if (is_array($el)) {
                    return array_merge($carry, self::toChildArray($el));
                }

                if ($el === null || is_bool($el) || $el === '') {
                    return $carry;
                }

                $carry[] = $el;

                return $carry;
            },
            [],
        );
    }

    /** @param array<mixed> $props */
    public function __construct(
        public readonly string | null $key,
        public readonly mixed $type,
        public readonly array $props,
    ) {
    }
}
