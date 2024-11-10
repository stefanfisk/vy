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
            id: -1,
            parent: null,
            key: null,
            type: '',
        );

        $component = fn () => null;

        $node = new Node(
            id: 0,
            parent: $parent,
            key: 'key',
            type: $component,
        );

        $this->assertSame(0, $node->id);
        $this->assertSame($parent, $node->parent);
        $this->assertSame(1, $node->depth);
        $this->assertSame('key', $node->key);
        $this->assertSame($component, $node->type);
        $this->assertSame(Node::STATE_INITIAL, $node->state);
        $this->assertNull($node->nextProps);
        $this->assertNull($node->props);
        $this->assertSame([], $node->hooks);
        $this->assertSame([], $node->children);
    }
}
