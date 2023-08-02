<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class MockInvokable
{
    public function __construct(
        private readonly Invokable&MockObject $invokableMock,
    ) {
    }

    /** @return InvocationMocker<Invokable> */
    public function expects(InvocationOrder $invocationRule): InvocationMocker
    {
        return $this->invokableMock
            ->expects($invocationRule)
            ->method('__invoke');
    }

    public function __invoke(mixed ...$args): mixed
    {
        return ($this->invokableMock)(...$args);
    }
}
