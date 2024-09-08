<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support\Mocks;

use Closure;
use Mockery\Expectation;
use Mockery\MockInterface;
use StefanFisk\Vy\Element;

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

    /**
     * @param array<mixed> $props
     */
    public function el(array $props = []): Element
    {
        return new Element($this->render(...), $props);
    }

    /**
     * @param array<mixed> $props
     */
    public function render(array $props): mixed
    {
        return ($this->mockInvokable)($props);
    }
}
