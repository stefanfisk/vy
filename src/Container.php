<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Psr\Container\ContainerInterface;
use StefanFisk\Vy\Errors\ContainerException;
use StefanFisk\Vy\Errors\EntryNotFoundException;
use Throwable;

use function class_exists;
use function sprintf;

class Container implements ContainerInterface
{
    public function has(string $id): bool
    {
        return class_exists($id);
    }

    public function get(string $id): mixed
    {
        if (! class_exists($id)) {
            throw new EntryNotFoundException(
                message: $id,
            );
        }

        try {
            /** @psalm-suppress MixedMethodCall */
            return new $id();
        } catch (Throwable $e) {
            throw new ContainerException(
                message: sprintf('Failed to instantiate `%s`.', $id),
                previous: $e,
            );
        }
    }
}
