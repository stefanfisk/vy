<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html;

use Override;

use function assert;
use function preg_match;
use function preg_replace;
use function strtolower;

final class DefaultPropToAttrNameMapper implements PropToAttrNameMapper
{
    #[Override]
    public function propToAttrName(string $propName): string
    {
        if (preg_match('/^[a-z0-9:-]+$/', $propName)) {
            return $propName;
        }

        $attrName = preg_replace('/[A-Z]/', '-$0', $propName);

        assert($attrName !== null);
        assert($attrName !== '');

        $attrName = strtolower($attrName);

        return $attrName;
    }
}
