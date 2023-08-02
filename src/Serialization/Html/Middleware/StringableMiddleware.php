<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Middleware;

use Stringable;

class StringableMiddleware extends TransformingMiddleware
{
    public function transformValue(mixed $value): mixed
    {
        if (!$value instanceof Stringable) {
            return $value;
        }

        return $value->__toString();
    }
}
