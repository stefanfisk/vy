<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use Closure;
use Mockery\Expectation;
use Mockery\MockInterface;

/**
 * This is a workaround for https://github.com/sebastianbergmann/phpunit/issues/5455.
 */
class MockComponent
{
    public function __construct(
        public readonly Invokable&MockInterface $mockInvokable,
        private readonly Closure $returnCallback,
    ) {
    }

    /**
     * @return Expectation
     */
    public function shouldReceiveRender()
    {
        return $this->mockInvokable
            ->shouldReceive('__invoke')
            ->andReturnUsing(fn ($props) => ($this->returnCallback)(...$props));
    }

    public function render(mixed ...$props): mixed
    {
        return ($this->mockInvokable)($props);
    }
}
