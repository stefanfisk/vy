<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use RuntimeException;

/**
 * Helper class for mocking closures.
 *
 * <code>
 * <?php
 *
 * $fn = $this->createMock(Invokable::class);
 *
 * $fn->expects($this->once())
 *     ->method('__invoke')
 *     ->with('foo')
 *     ->willReturn('bar');
 *
 * $this->sut->doThing($fn(...));
 * </code>
 */
class Invokable
{
    public function __invoke(): mixed
    {
        throw new RuntimeException(__FUNCTION__ . ' must be mocked.');
    }
}
