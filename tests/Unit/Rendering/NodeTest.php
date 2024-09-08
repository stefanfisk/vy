<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Rendering;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    public function testConstructorsSetsProperties(): void
    {
        $parent = new Node(
            parent: null,
            key: null,
            type: 'parent',
        );

        $node = new Node(
            parent: $parent,
            key: 'key',
            type: 'type',
        );

        $this->assertSame($parent, $node->parent);
        $this->assertSame(1, $node->depth);
        $this->assertSame('key', $node->key);
        $this->assertSame('type', $node->type);
        $this->assertSame(Node::STATE_INITIAL, $node->state);
        $this->assertNull($node->nextProps);
        $this->assertNull($node->props);
        $this->assertSame([], $node->hooks);
        $this->assertSame([], $node->children);
    }
}
