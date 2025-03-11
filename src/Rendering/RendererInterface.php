<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use StefanFisk\Vy\Hooks\HookHandlerInterface;

interface RendererInterface extends HookHandlerInterface
{
    public function valuesAreEqual(mixed $a, mixed $b): bool;

    public function enqueueRender(Node $node): void;
}
