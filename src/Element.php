<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function array_merge;
use function array_reduce;
use function is_array;
use function is_bool;

final class Element
{
    /**
     * @param non-empty-string|Closure $type
     * @param array<mixed> $props
     * @param ?non-empty-string $key
     */
    public static function create(string | Closure $type, array $props = [], ?string $key = null): self
    {
        return new self(
            type: $type,
            key: $key,
            props: $props,
        );
    }

    /** @return list<mixed> */
    public static function toChildArray(mixed $renderChildren): array
    {
        // Flatten the render children and remove empty items

        if (! is_array($renderChildren)) {
            $renderChildren = [$renderChildren];
        }

        // @phpstan-ignore return.type
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

    public function __invoke(mixed ...$children): Element
    {
        $props = $this->props;

        $oldChildren = $props['children'] ?? null;

        if ($oldChildren !== null) {
            throw new InvalidArgumentException('Element already has children.');
        }

        $props['children'] = $children;

        return new Element(
            key: $this->key,
            type: $this->type,
            props: $props,
        );
    }
}
