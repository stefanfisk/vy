<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

/**
 * @codeCoverageIgnore
 */
final class Diff
{
    /**
     * @param list<DiffChild> $newChildren
     * @param list<Node> $nodesToUnmount
     */
    public function __construct(
        public readonly array $newChildren,
        public readonly array $nodesToUnmount,
    ) {
    }
}
