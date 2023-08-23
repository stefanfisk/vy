<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support;

use function uniqid;

class DebugObject
{
    public readonly string $id;

    public function __construct(int | string | null $id)
    {
        $this->id = (string) ($id ?? uniqid());
    }
}
