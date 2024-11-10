<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function is_int;
use function is_string;

/**
 * @param array<mixed> $props
 */
// phpcs:ignore Squiz.Functions.GlobalFunction.Found
function el(string | Closure $type = '', array $props = []): Element
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

    // Create

    return new Element(
        type: $type,
        key: $key,
        props: $props,
    );
}
