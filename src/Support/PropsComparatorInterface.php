<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Support;

interface PropsComparatorInterface
{
    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    public function propsAreEqual(array $a, array $b): bool;
}
