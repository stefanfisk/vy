<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use PHPUnit\Framework\MockObject\MockBuilder;

trait MocksInvokablesTrait
{
    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $className
     * @psalm-return MockBuilder<RealInstanceType>
     */
    abstract public function getMockBuilder(string $className): MockBuilder;

    protected function createInvokableMock(): MockInvokable
    {
        $invokableMock = $this->getMockBuilder(Invokable::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();

        return new MockInvokable(
            invokableMock: $invokableMock,
        );
    }
}
