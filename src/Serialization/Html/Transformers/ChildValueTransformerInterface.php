<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Transformers;

interface ChildValueTransformerInterface
{
    public function processChildValue(mixed $value): mixed;
}
