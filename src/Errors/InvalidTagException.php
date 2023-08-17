<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Errors;

use StefanFisk\PhpReact\Rendering\Node;
use Throwable;

/** @psalm-api */
class InvalidTagException extends RenderException
{
    public function __construct(
        string $message,
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
