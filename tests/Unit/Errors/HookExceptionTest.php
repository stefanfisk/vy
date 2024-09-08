<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Errors\HookException;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(HookException::class)]
class HookExceptionTest extends TestCase
{
    public function testConstructorsSetsProperties(): void
    {
        $node = new Node(
            parent: null,
            key: null,
            type: '',
        );

        $previous = new Exception();

        $e = new HookException(
            message: 'Message.',
            hook: ContextHook::class,
            node: $node,
            previous: $previous,
        );

        $this->assertSame('Message.', $e->getMessage());
        $this->assertSame(ContextHook::class, $e->hook);
        $this->assertSame($node, $e->node);
        $this->assertSame($previous, $e->getPrevious());
    }
}
