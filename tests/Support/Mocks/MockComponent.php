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

    public function el(mixed ...$props): Element
    {
        return Element::create($this->render(...), $props);
    }

    public function render(mixed ...$props): mixed
    {
        return ($this->mockInvokable)($props);
    }
}
