<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Errors\InvalidNodeValueException;
use StefanFisk\PhpReact\Node;
use stdClass;

#[CoversClass(InvalidNodeValueException::class)]
class InvalidNodeValueExceptionTest extends TestCase
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

        $value = new stdClass();

        $previous = new Exception();

        $e = new InvalidNodeValueException(
            message: 'Message.',
            node: $node,
            value: $value,
            previous: $previous,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame($node, $e->node);
        $this->assertSame($value, $e->value);
        $this->assertSame($previous, $e->getPrevious());
    }
}
