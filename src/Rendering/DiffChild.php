<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

/**
 * @codeCoverageIgnore
 */
class DiffChild
{
    /**
     * @param mixed $renderChild The input element or value.
     * @param ?Node $oldChild The matched old child node, or null when the child is new.
     */
    public function __construct(
        public readonly ?Node $oldChild,
        public readonly mixed $renderChild,
    ) {
    }
}
