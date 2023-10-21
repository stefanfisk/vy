<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;
use StefanFisk\Vy\Components\Fragment;

use function is_int;
use function is_string;

/**
 * @param string|Closure|object|class-string $type
 * @param array<mixed> $props
 */
// phpcs:ignore Squiz.Functions.GlobalFunction.Found
function el(mixed $type, array $props = []): Element
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

    // Create

    return new Element($key, $type, $props);
}
