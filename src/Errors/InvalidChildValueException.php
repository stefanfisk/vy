<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use StefanFisk\Vy\Rendering\Node;
use Throwable;

final class InvalidChildValueException extends RenderException
{
    public function __construct(
        string $message,
        Node $node,
        public readonly mixed $inValue,
        public readonly mixed $value,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            node: $node,
            previous: $previous,
        );
    }
}
