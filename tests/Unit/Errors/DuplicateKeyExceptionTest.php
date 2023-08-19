<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Errors;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Errors\DuplicateKeyException;
use StefanFisk\PhpReact\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\PhpReact\Tests\TestCase;

#[CoversClass(DuplicateKeyException::class)]
class DuplicateKeyExceptionTest extends TestCase
{
    use CreatesStubNodesTrait;

    public function testConstructorsSetsProperties(): void
    {
        $node = $this->createStubNode();

        $el1 = new Element(key: null, type: null, props: []);
        $el2 = new Element(key: null, type: null, props: []);

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
