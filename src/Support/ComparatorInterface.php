<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Support;

interface ComparatorInterface
{
    public function valuesAreEqual(mixed $a, mixed $b): bool;
}
