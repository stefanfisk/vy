<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use StefanFisk\Vy\Hooks\Hook;
use StefanFisk\Vy\Rendering\Node;
use Throwable;

class HookException extends RenderException
{
    /** @param class-string<Hook> $hook */
    public function __construct(
        string $message,
        public readonly string $hook,
        Node $node,
        Throwable | null $previous = null,
    ) {
        parent::__construct(
            message: $message,
            node: $node,
            previous: $previous,
        );
    }
}
