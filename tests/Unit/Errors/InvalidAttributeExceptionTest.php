<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Errors\InvalidAttributeException;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Tests\TestCase;
use stdClass;

#[CoversClass(InvalidAttributeException::class)]
class InvalidAttributeExceptionTest extends TestCase
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

        $e = new InvalidAttributeException(
            message: 'Message.',
            node: $node,
            name: 'name',
            value: $value,
            previous: $previous,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame($node, $e->node);
        $this->assertSame('name', $e->name);
        $this->assertSame($value, $e->value);
        $this->assertSame($previous, $e->getPrevious());
    }
}
