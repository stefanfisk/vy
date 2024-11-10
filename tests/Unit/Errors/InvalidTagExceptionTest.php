<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Errors\InvalidTagException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(InvalidTagException::class)]
class InvalidTagExceptionTest extends TestCase
{
    public function testConstructorsSetsProperties(): void
    {
        $node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: '',
        );

        $previous = new Exception();

        $e = new InvalidTagException(
            message: 'Message.',
            node: $node,
            previous: $previous,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame($node, $e->node);
        $this->assertSame($previous, $e->getPrevious());
    }
}
