<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html;

interface PropToAttrNameMapper
{
    /**
     * @param non-empty-string $propName
     *
     * @return ?non-empty-string
     */
    public function propToAttrName(string $propName): ?string;
}
