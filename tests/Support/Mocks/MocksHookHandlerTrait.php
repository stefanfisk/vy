<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use StefanFisk\PhpReact\Hooks\Hook;
use StefanFisk\PhpReact\Hooks\HookHandlerInterface;

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
        $this->assertSame(
            $this->hookHandler,
            Hook::popHandler(),
        );
    }
}
