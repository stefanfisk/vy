<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html\Transformers;

interface AttributesTransformerInterface
{
    /**
     * @param array<non-empty-string,mixed> $attributes
     *
     * @return array<non-empty-string,mixed>
     */
    public function processAttributes(array $attributes): array;
}
