<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Errors;

use Exception;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Rendering\Node;

/**
 * Thrown when render returns multiple children with the same key.
 *
 * @psalm-api */
class DuplicateKeyException extends Exception
{
    public function __construct(
        string $message,
        public readonly Element $el1,
        public readonly Element $el2,
        public readonly Node | null $parentNode = null,
    ) {
        parent::__construct(
            $message,
            0,
        );
    }
}
