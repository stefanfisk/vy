<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

abstract class BaseElement
{
    /**
     * @param non-empty-string|Closure $type
     * @param array<mixed> $props
     * @param ?non-empty-string $key
     */
    public function __construct(
        public readonly string | Closure $type,
        public readonly array $props = [],
        public readonly ?string $key = null,
    ) {
        /** @psalm-suppress TypeDoesNotContainType */
        if ($type === '') {
            throw new InvalidArgumentException("$type cannot be empty string.");
        }

        /** @psalm-suppress TypeDoesNotContainType */
        if ($key === '') {
            throw new InvalidArgumentException("$key cannot be empty string.");
        }
    }
}
