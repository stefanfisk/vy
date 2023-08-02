<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Errors;

use Exception;
use StefanFisk\PhpReact\Node;
use Throwable;

/** @psalm-api */
class RenderException extends Exception
{
    public function __construct(
        string $message,
        public readonly Node | null $node = null,
        public readonly mixed $el = null,
        Throwable | null $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
