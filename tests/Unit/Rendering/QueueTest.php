<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Rendering\Queue;

#[CoversClass(Queue::class)]
class QueueTest extends TestCase
{
    private Queue $queue;

    private int $nextNodeId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = new Queue();
    }

    private function createNode(int $depth = 0): Node
    {
        $parent = null;

        if ($depth > 0) {
            $parent = $this->createNode(depth: $depth - 1);
        }

        return new Node(
            id: $this->nextNodeId++,
            parent: $parent,
            key: null,
            type: null,
            component: null,
        );
    }

    public function testNewQueueIsEmpty(): void
    {
        $this->assertTrue($this->queue->isEmpty());
    }

    public function testNewQueuePollReturnsNull(): void
    {
        $this->assertNull($this->queue->poll());
    }

    public function testPollReturnsNodesOfSameDepthInInsertionOrder(): void
    {
        $count = 10;
        $nodes = [];

        for ($i = 0; $i < $count; $i++) {
            $node = $this->createNode();

            $nodes[] = $node;
            $this->queue->insert($node);
        }

        for ($i = 0; $i < $count; $i++) {
            $node = $this->queue->poll();

            $this->assertSame($nodes[$i], $node);
        }
    }

    public function testPollReturnsNodesOfLowerDepthFirst(): void
    {
        $count = 10;
        $nodes = [];

        for ($i = 0; $i < $count; $i++) {
            $node = $this->createNode(depth: $count - $i);

            $nodes[] = $node;
            $this->queue->insert($node);
        }

        for ($i = $count - 1; $i >= 0; $i--) {
            $node = $this->queue->poll();

            $this->assertSame($nodes[$i], $node);
        }
    }
}
