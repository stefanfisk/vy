<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use Closure;
use ReflectionFunction;
use StefanFisk\Vy\Element;

use function count;
use function current;
use function is_array;
use function key;
use function next;
use function reset;

final class Comparator
{
    public function valuesAreEqual(mixed $a, mixed $b): bool
    {
        return match (true) {
            $a === $b => true,
            is_array($a) && is_array($b) => $this->arraysAreEqual($a, $b),
            $a instanceof Element && $b instanceof Element => $this->elsAreEqual($a, $b),
            $a instanceof Closure && $b instanceof Closure => $this->closuresAreEqual($a, $b),
            default => false,
        };
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

        if (!$this->valuesAreEqual($a->type, $b->type)) {
            return false;
        }

        return $this->arraysAreEqual($a->props, $b->props);
    }

    private function closuresAreEqual(Closure $a, Closure $b): bool
    {
        $refA = new ReflectionFunction($a);
        $refB = new ReflectionFunction($b);

        // Check if closures are from the same file and lines

        if (
            $refA->getFileName() !== $refB->getFileName() ||
            $refA->getStartLine() !== $refB->getStartLine() ||
            $refA->getEndLine() !== $refB->getEndLine()
        ) {
            return false;
        }

        // Check static variables

        if ($refA->getStaticVariables() !== $refB->getStaticVariables()) {
            return false;
        }

        // Check parameter count and details

        $params1 = $refA->getParameters();
        $params2 = $refB->getParameters();

        if (count($params1) !== count($params2)) {
            return false;
        }

        foreach ($params1 as $i => $param1) {
            $param2 = $params2[$i];

            if ($param1->getName() !== $param2->getName()) {
                return false;
            }

            if ((string) $param1->getType() !== (string) $param2->getType()) {
                return false;
            }

            if ($param1->isDefaultValueAvailable() !== $param2->isDefaultValueAvailable()) {
                return false;
            }

            if ($param1->isDefaultValueAvailable() && $param1->getDefaultValue() !== $param2->getDefaultValue()) {
                return false;
            }
        }

        // Check binding context

        if ($refA->getClosureThis() !== $refB->getClosureThis()) {
            return false;
        }

        // Check static class

        // phpcs:ignore SlevomatCodingStandard.ControlStructures.UselessIfConditionWithReturn.UselessIfCondition
        if ($refA->getClosureCalledClass()?->getName() !== $refB->getClosureCalledClass()?->getName()) {
            return false;
        }

        // Looks equal!

        return true;
    }
}
