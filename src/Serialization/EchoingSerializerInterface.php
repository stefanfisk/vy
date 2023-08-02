<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization;

use StefanFisk\PhpReact\Node;

/** @extends SerializerInterface<null> */
interface EchoingSerializerInterface extends SerializerInterface
{
    public function serialize(Node $node): mixed;
}
