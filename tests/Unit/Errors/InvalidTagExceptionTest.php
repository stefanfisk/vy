<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Errors\InvalidTagException;
use StefanFisk\PhpReact\Node;

#[CoversClass(InvalidTagException::class)]
class InvalidTagExceptionTest extends TestCase
{
    public function testConstructorsSetsProperties(): void
    {
        $node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: null,
            component: null,
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
