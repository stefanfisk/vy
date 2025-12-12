<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use RuntimeException;
use StefanFisk\Vy\Context;

final class ContextHasNoDefaultValueException extends RuntimeException
{
    /**
     * @param Context<T> $context
     *
     * @template T
     */
    public function __construct(
        public readonly Context $context,
    ) {
        parent::__construct("No default was was provided for context $context->name.");
    }
}
