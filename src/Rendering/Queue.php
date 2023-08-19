<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

use function array_filter;
use function array_splice;
use function array_values;
use function assert;
use function count;

/**
 * A simple priority queue.
 *
 * Nodes with lower depth are returned first. Nodes of equal depth are returned in insertion order.
 */
class Queue
{
    /** @var array<Node> */
    private array $queue = [];

    public function insert(Node $node): void
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));

        if ($node->state & Node::STATE_ENQUEUED) {
            return;
        }

        $this->queue[] = $node;

        $node->state |= Node::STATE_ENQUEUED;
    }

    public function remove(Node $node): void
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));

        if (!($node->state & Node::STATE_ENQUEUED)) {
            return;
        }

        $this->queue = array_values(array_filter($this->queue, fn ($n) => $n !== $node));

        $node->state &= ~Node::STATE_ENQUEUED;
    }

    public function poll(): Node | null
    {
        if (!$this->queue) {
            return null;
        }

        // Find the first node with the lowest depth and render it

        $count = count($this->queue);
        $i = 0;
        $a = $this->queue[$i];

        for ($j = 1; $j < $count; $j++) {
            $b = $this->queue[$j];

            if ($a->depth <= $b->depth) {
                continue;
            }

            $i = $j;
            $a = $b;
        }

        array_splice($this->queue, $i, 1);

        assert((bool) ($a->state & Node::STATE_ENQUEUED));
        assert(!($a->state & Node::STATE_UNMOUNTED));

        $a->state &= ~Node::STATE_ENQUEUED;

        return $a;
    }
}
