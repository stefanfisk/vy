<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

use Override;
use StefanFisk\Vy\Serialization\Html\HtmlableInterface;
use Stringable;

final class StringableTransformer extends ValueTransformer
{
    #[Override]
    public function transformValue(mixed $value): mixed
    {
        if (!$value instanceof Stringable || $value instanceof HtmlableInterface) {
            return $value;
        }

        return $value->__toString();
    }
}
