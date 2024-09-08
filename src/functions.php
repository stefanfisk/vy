<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;

/**
 * @param string|Context<mixed>|Closure(T):mixed $type
 * @param T $props
 *
 * @template T of array
 */
// phpcs:ignore Squiz.Functions.GlobalFunction.Found
function el(string | Context | Closure $type = '', array $props = []): Element
{
    return new Element($type, $props);
}
