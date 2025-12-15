<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function array_walk_recursive;
use function get_debug_type;
use function is_array;
use function is_bool;
use function is_string;
use function sprintf;

final class Element
{
    public const KEY = '@@key';

    /**
     * @param non-empty-string|Closure $type
     * @param array<non-empty-string,mixed> $props
     * @param ?non-empty-string $key
     */
    public static function create(string | Closure $type, array $props = [], ?string $key = null): self
    {
        return new self($type, $props, $key);
    }

    /** @return list<mixed> */
    public static function toChildArray(mixed $inChildren): array
    {
        // Flatten the render children and remove empty items

        if (! is_array($inChildren)) {
            $inChildren = [$inChildren];
        }

        $children = [];

        array_walk_recursive($inChildren, function (mixed $child) use (&$children) {
            if ($child === null || is_bool($child) || $child === '') {
                return;
            }

            $children[] = $child;
        });

        return $children;
    }

    /** @var array<non-empty-string,mixed> */
    public readonly array $props;

    /** @var ?non-empty-string */
    public readonly ?string $key;

    /**
     * @param non-empty-string|Closure $type
     * @param array<non-empty-string,mixed> $props
     * @param ?non-empty-string $key
     */
    public function __construct(
        public readonly string | Closure $type,
        array $props = [],
        ?string $key = null,
    ) {
        /** @psalm-suppress TypeDoesNotContainType */
        if ($type === '') {
            throw new InvalidArgumentException('$type cannot be empty string.');
        }

        /** @psalm-suppress TypeDoesNotContainType */
        if ($key === '') {
            throw new InvalidArgumentException('$key cannot be empty string.');
        }

        $propKey = $props[self::KEY] ?? null;
        if ($propKey !== null) {
            if (!is_string($propKey)) {
                throw new InvalidArgumentException(
                    sprintf('Key must be be non-empty string, got %s.', get_debug_type($key)),
                );
            }

            if ($propKey === '') {
                throw new InvalidArgumentException('$key cannot be empty string.');
            }

            if ($key !== null && $key !== $propKey) {
                throw new InvalidArgumentException(
                    'Both argument key and prop key were passed but their values did not match.',
                );
            }

            $key = $propKey;
        }
        unset($props[self::KEY]);

        $this->props = $props;
        $this->key = $key;
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
