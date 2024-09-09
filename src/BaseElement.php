<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

abstract class BaseElement
{
    /**
     * @param ?non-empty-string $key
     * @param array<mixed> $props
     */
    public function __construct(
        public readonly string | Closure $type,
        public readonly ?string $key = null,
        public readonly array $props = [],
    ) {
        /** @psalm-suppress TypeDoesNotContainType */
        if ($key === '') {
            throw new InvalidArgumentException("$key cannot be empty string.");
        }
    }
}
