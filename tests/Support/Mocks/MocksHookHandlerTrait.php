<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use StefanFisk\PhpReact\Hooks\Hook;
use StefanFisk\PhpReact\Hooks\HookHandlerInterface;

trait MocksHookHandlerTrait
{
    protected HookHandlerInterface&MockObject $hookHandler;

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $className
     * @psalm-return MockBuilder<RealInstanceType>
     */
    abstract public function getMockBuilder(string $className): MockBuilder;

    #[Before]
    protected function setUpMocksHookHandlerTrait(): void
    {
        $this->hookHandler = $this->getMockBuilder(HookHandlerInterface::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();
        Hook::setHandler($this->hookHandler);
    }

    #[After]
    protected function tearDownMocksHookHandlerTrait(): void
    {
        Hook::setHandler(null);
    }
}
