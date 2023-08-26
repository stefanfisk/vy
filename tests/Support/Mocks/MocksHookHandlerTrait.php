<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support\Mocks;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use StefanFisk\Vy\Hooks\Hook;
use StefanFisk\Vy\Hooks\HookHandlerInterface;

trait MocksHookHandlerTrait
{
    use MockeryTrait;

    protected HookHandlerInterface&MockInterface $hookHandler;

    #[Before]
    protected function setUpMocksHookHandlerTrait(): void
    {
        $this->hookHandler = $this->mockery(HookHandlerInterface::class);
        Hook::pushHandler($this->hookHandler);
    }

    #[After]
    protected function tearDownMocksHookHandlerTrait(): void
    {
        if (Hook::getHandler() !== $this->hookHandler) {
            $this->assertSame($this->hookHandler, Hook::getHandler());
        }

        Hook::popHandler();
    }
}
