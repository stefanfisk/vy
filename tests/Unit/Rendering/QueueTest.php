<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use AssertionError;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Queue;
use StefanFisk\Vy\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(Queue::class)]
class QueueTest extends TestCase
{
    use CreatesStubNodesTrait;

    private Queue $queue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = new Queue();
    }

    public function testNewQueuePollReturnsNull(): void
    {
        $this->assertNull($this->queue->poll());
    }

    public function testInsertAssertsThatNodeIsMounted(): void
    {
        $node = $this->createStubNode(depth: 0);
        $node->state = Node::STATE_UNMOUNTED;

        $this->expectException(AssertionError::class);

        $this->queue->insert($node);
    }

    public function testInsertSetsStateEnqueued(): void
    {
        $node = $this->createStubNode(depth: 0);

        $this->queue->insert($node);

        $this->assertSame(Node::STATE_ENQUEUED, $node->state);
    }

    public function testPollReturnsNodesOfSameDepthInInsertionOrder(): void
    {
        $node1 = $this->createStubNode(depth: 0);
        $node2 = $this->createStubNode(depth: 0);
        $node3 = $this->createStubNode(depth: 0);

        $this->queue->insert($node1);
        $this->queue->insert($node2);
        $this->queue->insert($node3);

        $this->assertSame($node1, $this->queue->poll());
        $this->assertSame($node2, $this->queue->poll());
        $this->assertSame($node3, $this->queue->poll());
        $this->assertNull($this->queue->poll());
    }

    public function testPollReturnsNodesOfLowerDepthFirst(): void
    {
        $node1 = $this->createStubNode(depth: 0);
        $node2 = $this->createStubNode(depth: 1);
        $node3 = $this->createStubNode(depth: 2);
        $node4 = $this->createStubNode(depth: 1);
        $node5 = $this->createStubNode(depth: 0);

        $this->queue->insert($node1);
        $this->queue->insert($node2);
        $this->queue->insert($node3);
        $this->queue->insert($node4);
        $this->queue->insert($node5);

        $this->assertSame($node1, $this->queue->poll());
        $this->assertSame($node5, $this->queue->poll());
        $this->assertSame($node2, $this->queue->poll());
        $this->assertSame($node4, $this->queue->poll());
        $this->assertSame($node3, $this->queue->poll());
        $this->assertNull($this->queue->poll());
    }

    public function testInsertingNodeAlreadyEnqueuedNodeDoesNothing(): void
    {
        $node1 = $this->createStubNode(depth: 0);
        $node2 = $this->createStubNode(depth: 0);

        $this->queue->insert($node1);
        $this->queue->insert($node2);
        $this->queue->insert($node1);
        $this->queue->insert($node2);

        $this->assertSame($node1, $this->queue->poll());
        $this->assertSame($node2, $this->queue->poll());
        $this->assertNull($this->queue->poll());
    }

    public function testReinsertingPolledNode(): void
    {
        $node1 = $this->createStubNode(depth: 1);
        $node2 = $this->createStubNode(depth: 2);
        $node3 = $this->createStubNode(depth: 3);

        $this->queue->insert($node1);
        $this->queue->insert($node2);
        $this->queue->insert($node3);

        $this->assertSame($node1, $this->queue->poll());
        $this->queue->insert($node1);
        $this->assertSame($node1, $this->queue->poll());
        $this->assertSame($node2, $this->queue->poll());
        $this->assertSame($node3, $this->queue->poll());
        $this->assertNull($this->queue->poll());
    }

    public function testPollDoesNotReturnRemovedNodes(): void
    {
        $node1 = $this->createStubNode(depth: 0);
        $node2 = $this->createStubNode(depth: 0);
        $node3 = $this->createStubNode(depth: 0);

        $this->queue->insert($node1);
        $this->queue->insert($node2);
        $this->queue->insert($node3);

        $this->queue->remove($node2);

        $this->assertSame($node1, $this->queue->poll());
        $this->assertSame($node3, $this->queue->poll());
        $this->assertNull($this->queue->poll());
    }

    public function testRemoveAssertsThatNodeIsMounted(): void
    {
        $node = $this->createStubNode(depth: 0);

        $node->state = Node::STATE_UNMOUNTED;

        $this->expectException(AssertionError::class);

        $this->queue->remove($node);
    }

    public function testRemoveAssertsThatNodeWithStateEnqueuedIsInQueue(): void
    {
        $node = $this->createStubNode(depth: 0);
        $node->state = Node::STATE_ENQUEUED;

        $this->expectException(AssertionError::class);

        $this->queue->remove($node);
    }

    public function testRemoveDoesNothingIfNodeIsNotEnqueued(): void
    {
        $node = $this->createStubNode(depth: 0);

        $this->queue->remove($node);

        $this->assertNull($this->queue->poll());
    }

    public function testPollAssertsThatNodeIsEnqueued(): void
    {
        $node = $this->createStubNode(depth: 0);

        $this->queue->insert($node);

        $node->state = Node::STATE_NONE;

        $this->expectException(AssertionError::class);

        $this->queue->poll();
    }

    public function testRemoveUnsetsStateEnqueued(): void
    {
        $node = $this->createStubNode(depth: 0);

        $this->queue->insert($node);

        $this->queue->remove($node);

        $this->assertSame(Node::STATE_NONE, $node->state);
    }

    public function testPollAssertsThatNodeIsMounted(): void
    {
        $node = $this->createStubNode(depth: 0);

        $this->queue->insert($node);

        $node->state = Node::STATE_UNMOUNTED;

        $this->expectException(AssertionError::class);

        $this->queue->poll();
    }

    public function testPollUnsetsStateEnqueued(): void
    {
        $node = $this->createStubNode(depth: 0);

        $this->queue->insert($node);

        $this->queue->poll();

        $this->assertSame(Node::STATE_NONE, $node->state);
    }
}
