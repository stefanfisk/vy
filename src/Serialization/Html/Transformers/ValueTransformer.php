<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

use Override;

abstract class ValueTransformer implements AttributesTransformerInterface, ChildValueTransformerInterface
{
    /** {@inheritDoc} */
    #[Override]
    final public function processAttributes(array $attributes): array
    {
        $newAttributes = [];

        foreach ($attributes as $name => $value) {
            $newValue = $this->transformValue($value);

            $newAttributes[$name] = $newValue;
        }

        return $newAttributes;
    }

    #[Override]
    final public function processChildValue(mixed $value): mixed
    {
        return $this->transformValue($value);
    }

    abstract public function transformValue(mixed $value): mixed;
}
