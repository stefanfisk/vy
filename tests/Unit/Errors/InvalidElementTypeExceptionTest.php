<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Errors\InvalidElementTypeException;
use StefanFisk\PhpReact\Node;

#[CoversClass(InvalidElementTypeException::class)]
class InvalidElementTypeExceptionTest extends TestCase
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

        $el = new Element(key: 'key', type: 'type', props: []);

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