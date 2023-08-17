<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

use function array_filter;
use function array_splice;
use function count;

class Queue
{
    /** @var array<Node> */
    private array $queue = [];

    public function insert(Node $node): void
    {
        $this->queue[] = $node;
    }

    public function remove(Node $node): void
    {
        $this->queue = array_filter($this->queue, fn ($n) => $n !== $node);
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

        return $a;
    }
}
