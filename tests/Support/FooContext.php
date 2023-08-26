<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support;

use StefanFisk\Vy\Components\Context;

class FooContext extends Context
{
    public static function getDefaultValue(): string
    {
        return 'foo';
    }

    public static function use(): string
    {
        /** @phpstan-ignore-next-line */
        return (string) parent::use();
    }
}
