<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use Closure;

trait MocksComponentsTrait
{
    use MockeryTrait;
    use MocksInvokablesTrait;

    protected function createComponentMock(Closure $returnCallback): MockComponent
    {
        return new MockComponent(
            mockInvokable: $this->createMockInvokable(),
            returnCallback: $returnCallback,
        );
    }
}
