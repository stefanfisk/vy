<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function array_walk_recursive;
use function is_array;
use function is_bool;

final class Element extends BaseElement
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

    public function __invoke(mixed ...$children): self
    {
        $props = $this->props;

        $oldChildren = $props['children'] ?? null;

        if ($oldChildren !== null) {
            throw new InvalidArgumentException('Element already has children.');
        }

        $props['children'] = $children;

        return new self(
            key: $this->key,
            type: $this->type,
            props: $props,
        );
    }
}
