<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Components;

use InvalidArgumentException;
use StefanFisk\Vy\Element;

use function StefanFisk\Vy\el;
use function array_filter;
use function array_reverse;
use function is_bool;

class Compose
{
    /**
     * @param array<Element|bool|null> $elements
     */
    public static function el(array $elements): Element
    {
        return el(self::render(...), [
            'elements' => $elements,
        ]);
    }

    /**
     * @param array{elements:array<Element|bool|null>,children?:mixed} $props
     */
    private static function render(array $props): mixed
    {
        $elements = $props['elements'] ?? [];
        $children = $props['children'] ?? null;

        // Filter non-elements

        /** @var array<Element> $elements */
        $elements = array_filter($elements, function ($el) {
            if ($el instanceof Element) {
                return true;
            } elseif (is_bool($el)) {
                return false;
            } elseif ($el === null) {
                return false;
            } else {
                throw new InvalidArgumentException('$elements must only contain Element, bool or null.');
            }
        });

        // Return a component that applies the elements in reverses order

        $elements = array_reverse($elements);

        $el = $children;

        foreach ($elements as $el2) {
            $el = $el2($el);
        }

        return $el;
    }
}
