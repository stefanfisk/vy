<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Components;

final class Fragment
{
    public function render(mixed $children = null): mixed
    {
        return $children;
    }
}
