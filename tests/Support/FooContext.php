<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support;

use StefanFisk\Vy\IsStaticContextTrait;

final class FooContext
{
    /** @use IsStaticContextTrait<string> */
    use IsStaticContextTrait;

    protected static function getDefaultValue(): mixed
    {
        return 'foo';
    }
}
