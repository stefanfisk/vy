<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use Exception;
use StefanFisk\Vy\BaseElement;
use StefanFisk\Vy\Rendering\Node;
use Throwable;

/**
 * Thrown when encountering elements with an invalid type.
 */
final class InvalidElementTypeException extends Exception
{
    public function __construct(
        string $message,
        public readonly BaseElement $el,
        public readonly ?Node $parentNode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $message,
            0,
            previous: $previous,
        );
    }
}
