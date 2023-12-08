<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function array_merge;
use function array_reduce;
use function array_reverse;
use function assert;
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

    public static function compose(mixed ...$elements): Element
    {
        // Flatten and remove falsy and true values

        /** @var list<Element> $elements */
        $elements = array_reduce(
            $elements,
            function (array $carry, mixed $el) {
                if (is_array($el)) {
                    return array_merge($carry, self::toChildArray($el));
                }

                if (!$el || $el === true) {
                    return $carry;
                }

                if ($el instanceof Closure) {
                    $el = new Element(type: $el);
                }

                assert($el instanceof Element);

                $carry[] = $el;

                return $carry;
            },
            [],
        );

        // Return a component that applies the elements in reverses order

        $elements = array_reverse($elements);

        $compose = function (mixed $children = null) use ($elements): mixed {
            $el = $children;

            foreach ($elements as $el2) {
                $el = $el2($el);
            }

            return $el;
        };

        return new Element(type: $compose);
    }

    /** @param array<mixed> $props */
    public function __construct(
        public readonly mixed $type,
        public readonly string | null $key = null,
        public readonly array $props = [],
    ) {
    }

    public function __invoke(mixed ...$children): Element
    {
        $props = $this->props;

        if ($props['children'] ?? false) {
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
