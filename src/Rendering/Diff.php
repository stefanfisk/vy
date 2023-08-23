<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

/**
 * @codeCoverageIgnore
 */
class Diff
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
