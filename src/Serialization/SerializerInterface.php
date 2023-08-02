<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization;

use StefanFisk\PhpReact\Node;

/** @template T */
interface SerializerInterface
{
    /** @return T */
    public function serialize(Node $node): mixed;
}
