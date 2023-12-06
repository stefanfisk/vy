<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support;

use StefanFisk\Vy\Serialization\Html\PropToAttrNameMapper;

class PassthroughPropToAttrNameMapper implements PropToAttrNameMapper
{
    public function propToAttrName(string $propName): ?string
    {
        return $propName;
    }
}
