<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Components;

use InvalidArgumentException;
use StefanFisk\Vy\Element;

use function array_merge;
use function array_reduce;
use function array_reverse;
use function is_array;

class Compose
{
    /** @param list<mixed> $elements */
    public static function el(array $elements): Element
    {
        return new Element(
            type: self::class,
            props: ['elements' => $elements],
        );
    }

    /** @param list<mixed> $elements */
    public function render(
        array $elements = [],
        mixed $children = null,
    ): mixed {
        /** @var list<Element> $elements */
        $elements = array_reduce(
            $elements,
            function (array $carry, mixed $el) {
                if (is_array($el)) {
                    return array_merge($carry, Element::toChildArray($el));
                }

                if (!$el || $el === true) {
                    return $carry;
                }

                if (!$el instanceof Element) {
                    throw new InvalidArgumentException('$elements must be Element, bool or null.');
                }

                $carry[] = $el;

                return $carry;
            },
            [],
        );

        // Return a component that applies the elements in reverses order

        $elements = array_reverse($elements);

        $el = $children;

        foreach ($elements as $el2) {
            $el = $el2($el);
        }

        return $el;
    }
}
