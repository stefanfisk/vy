<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

abstract class ValueTransformer implements AttributeValueTransformerInterface, ChildValueTransformerInterface
{
    /** {@inheritDoc} */
    final public function processAttributeValue(string $name, mixed $value): mixed
    {
        return $this->transformValue($value);
    }

    /** {@inheritDoc} */
    final public function processChildValue(mixed $value): mixed
    {
        return $this->transformValue($value);
    }

    abstract public function transformValue(mixed $value): mixed;
}
