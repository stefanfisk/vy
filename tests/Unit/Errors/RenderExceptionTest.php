<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\RenderException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(RenderException::class)]
class RenderExceptionTest extends TestCase
{
    public function testConstructorsSetsProperties(): void
    {
        $node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: 'div',
        );

        $el = new Element(type: 'type', key: 'key');

        $previous = new Exception();

        $e = new RenderException(
            message: 'Message.',
            node: $node,
            el: $el,
            previous: $previous,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame($node, $e->node);
        $this->assertSame($el, $e->el);
        $this->assertSame($previous, $e->getPrevious());
    }
}
