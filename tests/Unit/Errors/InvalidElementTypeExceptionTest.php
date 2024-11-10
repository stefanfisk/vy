<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\InvalidElementTypeException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(InvalidElementTypeException::class)]
class InvalidElementTypeExceptionTest extends TestCase
{
    public function testConstructorsSetsProperties(): void
    {
        $node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: '',
        );

        $el = new Element(type: 'type', key: 'key');

        $previous = new Exception();

        $e = new InvalidElementTypeException(
            message: 'Message.',
            el: $el,
            parentNode: $node,
            previous: $previous,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame($el, $e->el);
        $this->assertSame($node, $e->parentNode);
        $this->assertSame($previous, $e->getPrevious());
    }
}
