<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Transformers;

interface AttributeValueTransformerInterface
{
    public function processAttributeValue(string $name, mixed $value): mixed;
}
