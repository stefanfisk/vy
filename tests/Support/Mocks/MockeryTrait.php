<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support\Mocks;

use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

trait MockeryTrait
{
    /**
     * Configures and returns a mock object with proper type-hinting
     * for static analysis tools
     *
     * @param class-string<TMock> $class
     *
     * @return LegacyMockInterface&MockInterface&TMock
     *
     * @template TMock
     */
    abstract public function mockery(string $class, mixed ...$arguments);
}
