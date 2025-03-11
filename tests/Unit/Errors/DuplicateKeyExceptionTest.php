<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Errors;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\DuplicateKeyException;
use StefanFisk\Vy\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(DuplicateKeyException::class)]
class DuplicateKeyExceptionTest extends TestCase
{
    use CreatesStubNodesTrait;

    public function testConstructorsSetsProperties(): void
    {
        $node = $this->createStubNode();

        $el1 = new Element(type: 'div');
        $el2 = new Element(type: 'div');

        $e = new DuplicateKeyException(
            message: 'Message.',
            el1: $el1,
            el2: $el2,
            parentNode: $node,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame($el1, $e->el1);
        $this->assertSame($el2, $e->el2);
        $this->assertSame($node, $e->parentNode);
    }
}
