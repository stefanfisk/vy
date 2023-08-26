<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization;

use StefanFisk\Vy\Rendering\Node;

/** @template T */
interface SerializerInterface
{
    /** @return T */
    public function serialize(Node $node): mixed;
}
