<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use Exception;
use StefanFisk\Vy\BaseElement;
use StefanFisk\Vy\Rendering\Node;

/**
 * Thrown when render returns multiple children with the same key.
 */
class DuplicateKeyException extends Exception
{
    public function __construct(
        string $message,
        public readonly BaseElement $el1,
        public readonly BaseElement $el2,
        public readonly ?Node $parentNode = null,
    ) {
        parent::__construct(
            $message,
            0,
        );
    }
}
