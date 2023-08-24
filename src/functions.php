<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact;

use Closure;

/**
 * @param string|Closure|object|class-string $type
 * @param array<mixed> $props
 *
 * @codeCoverageIgnore
 */
// phpcs:ignore Squiz.Functions.GlobalFunction.Found
function el(mixed $type, array $props = [], mixed ...$children): Element
{
    return Element::create($type, $props, ...$children);
}
