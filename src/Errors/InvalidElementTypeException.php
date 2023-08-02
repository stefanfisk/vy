<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Errors;

use Exception;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Node;
use Throwable;

/**
 * Thrown when encountering elements with an invalid type.
 *
 * @psalm-api */
class InvalidElementTypeException extends Exception
{
    public function __construct(
        string $message,
        public readonly Element $el,
        public readonly Node | null $parentNode = null,
        Throwable | null $previous = null,
    ) {
        parent::__construct(
            $message,
            0,
            previous: $previous,
        );
    }
}
