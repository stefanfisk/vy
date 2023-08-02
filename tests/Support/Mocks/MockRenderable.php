<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use Closure;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class MockRenderable
{
    public function __construct(
        private readonly Closure $returnCallback,
        private readonly Invokable&MockObject $mockInvokable,
    ) {
    }

    /** @return InvocationMocker<Invokable> */
    public function expects(InvocationOrder $invocationRule): InvocationMocker
    {
        return $this->mockInvokable
            ->expects($invocationRule)
            ->method('__invoke')
            ->willReturnCallback(fn ($props) => ($this->returnCallback)(...$props));
    }

    public function render(mixed ...$props): mixed
    {
        return ($this->mockInvokable)($props);
    }
}
