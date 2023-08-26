<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

interface ChildValueTransformerInterface
{
    public function processChildValue(mixed $value): mixed;
}
