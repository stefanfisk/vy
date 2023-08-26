<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support\Mocks;

use Closure;
use RuntimeException;

/**
 * Helper interface for mocking closures.
 *
 * <code>
 * <?php
 *
 * $myClosure = $this->mockery(Invokable::class, []);
 *
 * $myClosure
 *     ->shouldRecieve('__invoke')
 *     ->with('foo')
 *     ->once()
 *     ->andReturn('bar');
 *
 * $this->sut->doThing($myClosure->fn);
 * </code>
 */
class Invokable
{
    public Closure $fn;

    public function __construct()
    {
        $this->fn = $this->__invoke(...);
    }

    public function __invoke(mixed ...$args): mixed
    {
        throw new RuntimeException(__FUNCTION__ . ' must be mocked.');
    }
}
