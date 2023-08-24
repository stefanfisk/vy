<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Errors;

use StefanFisk\PhpReact\Hooks\Hook;
use StefanFisk\PhpReact\Rendering\Node;
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
