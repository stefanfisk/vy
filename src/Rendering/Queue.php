<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

use function array_search;
use function array_shift;
use function array_splice;
use function assert;
use function usort;

/**
 * A simple priority queue.
 *
 * Nodes with lower depth are returned first. Nodes of equal depth are returned in insertion order.
 *
 * Since insertions and removals are assumed to happen in bulk sorting is deferred until the next poll.
 */
class Queue
{
    /** @var list<Node> */
    private array $queue = [];

    private bool $isSorted = false;

    public function insert(Node $node): void
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));

        if ($node->state & Node::STATE_ENQUEUED) {
            return;
        }

        $this->queue[] = $node;

        $node->state |= Node::STATE_ENQUEUED;

        $this->isSorted = false;
    }

    public function remove(Node $node): void
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));

        if (!($node->state & Node::STATE_ENQUEUED)) {
            return;
        }

        $i = array_search($node, $this->queue, true);

        if ($i === false) {
            return;
        }

        array_splice($this->queue, $i, 1);

        $node->state &= ~Node::STATE_ENQUEUED;
    }

    public function poll(): Node | null
    {
        if (!$this->queue) {
            return null;
        }

        if (!$this->isSorted) {
            usort($this->queue, fn (Node $a, Node $b): int => $a->depth <=> $b->depth);

            $this->isSorted = true;
        }

        $node = array_shift($this->queue);

        assert((bool) ($node->state & Node::STATE_ENQUEUED));
        assert(!($node->state & Node::STATE_UNMOUNTED));

        $node->state &= ~Node::STATE_ENQUEUED;

        return $node;
    }
}
