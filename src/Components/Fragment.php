<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Components;

/** @psalm-api */
final class Fragment
{
    public function render(mixed $children = null): mixed
    {
        return $children;
    }
}
