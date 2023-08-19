<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Errors\HookException;
use StefanFisk\PhpReact\Hooks\ContextHook;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Tests\TestCase;

#[CoversClass(HookException::class)]
class HookExceptionTest extends TestCase
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
