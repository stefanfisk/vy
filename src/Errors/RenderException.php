<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use Exception;
use StefanFisk\Vy\Rendering\Node;
use Throwable;

class RenderException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?Node $node = null,
        public readonly mixed $el = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
