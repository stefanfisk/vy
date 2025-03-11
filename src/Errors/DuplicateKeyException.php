<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use Exception;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Rendering\Node;

/**
 * Thrown when render returns multiple children with the same key.
 */
final class DuplicateKeyException extends Exception
{
    public function __construct(
        string $message,
        public readonly Element $el1,
        public readonly Element $el2,
        public readonly ?Node $parentNode = null,
    ) {
        parent::__construct(
            $message,
            0,
        );
    }
}
