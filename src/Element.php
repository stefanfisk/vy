<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use InvalidArgumentException;

use function array_merge;
use function array_reduce;
use function is_array;
use function is_bool;

class Element
{
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

    /**
     * @param ?non-empty-string $key
     * @param array<mixed> $props
     */
    public function __construct(
        public readonly mixed $type,
        public readonly ?string $key = null,
        public readonly array $props = [],
    ) {
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
