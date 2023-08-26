<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use StefanFisk\Vy\Rendering\Node;
use Throwable;

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
