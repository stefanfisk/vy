<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use StefanFisk\Vy\Element;

use function count;
use function current;
use function is_array;
use function is_object;
use function key;
use function next;
use function reset;
use function spl_object_id;

class Comparator
{
    public function valuesAreEqual(mixed $a, mixed $b): bool
    {
        if (is_object($a) && is_object($b) && spl_object_id($a) === spl_object_id($b)) {
            return true;
        }

        if (is_array($a) && is_array($b)) {
            return $this->arraysAreEqual($a, $b);
        }

        if ($a instanceof Element && $b instanceof Element) {
            return $this->elsAreEqual($a, $b);
        }

        return $a === $b;
    }

    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function arraysAreEqual(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        reset($a);
        reset($b);

        while (true) {
            $aKey = key($a);
            $bKey = key($b);

            if ($aKey === null && $bKey === null) {
                break;
            }

            // This never happens because of the count comparison
            //
            // if ($aKey === null || $bKey === null) {
            //     return false;
            // }

            if ($aKey !== $bKey) {
                return false;
            }

            $aVal = current($a);
            $bVal = current($b);

            if (! $this->valuesAreEqual($aVal, $bVal)) {
                return false;
            }

            next($a);
            next($b);
        }

        return true;
    }

    private function elsAreEqual(Element $a, Element $b): bool
    {
        if ($a->key !== $b->key) {
            return false;
        }

        if ($a->type !== $b->type) {
            return false;
        }

        return $this->arraysAreEqual($a->props, $b->props);
    }
}
