<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Errors\InvalidChildValueException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(InvalidChildValueException::class)]
class InvalidChildValueExceptionTest extends TestCase
{
    public function testConstructorsSetsProperties(): void
    {
        $node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: 'div',
        );

        $inValue = new stdClass();
        $value = new stdClass();

        $previous = new Exception();

        $e = new InvalidChildValueException(
            message: 'Message.',
            node: $node,
            inValue: $inValue,
            value: $value,
            previous: $previous,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame($node, $e->node);
        $this->assertSame($inValue, $e->inValue);
        $this->assertSame($value, $e->value);
        $this->assertSame($previous, $e->getPrevious());
    }
}
