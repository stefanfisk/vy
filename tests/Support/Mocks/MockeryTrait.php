<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use Mockery\MockInterface;

trait MockeryTrait
{
    /**
     * Configures and returns a mock object with proper type-hinting
     * for static analysis tools
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments
     *
     * @return T & MockInterface
     *
     * @template T
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    abstract public function mockery(string $class, ...$arguments);
}
