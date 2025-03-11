<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support\Mocks;

use Mockery\MockInterface;

trait MocksInvokablesTrait
{
    use MockeryTrait;

    public function createMockInvokable(): Invokable & MockInterface
    {
        return $this->mockery(Invokable::class, []);
    }
}
