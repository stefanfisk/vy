<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

/**
 * @codeCoverageIgnore
 */
class DiffChild
{
    /**
     * @param mixed $renderChild The input element or value.
     * @param Node | null $oldChild The matched old child node, or null when the child is new.
     */
    public function __construct(
        public readonly Node | null $oldChild,
        public readonly mixed $renderChild,
    ) {
    }
}
