<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Transformers;

use Stringable;

class StringableTransformer extends ValueTransformer
{
    public function transformValue(mixed $value): mixed
    {
        if (!$value instanceof Stringable) {
            return $value;
        }

        return $value->__toString();
    }
}
