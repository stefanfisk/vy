<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use Closure;
use StefanFisk\Vy\Hooks\Hook;

class Node
{
    public const STATE_NONE = 0;
    /**
     * Set when the node is constructed and then unset after the first render attempt.
     *
     * This bit may only be unset by the renderer.
     */
    public const STATE_INITIAL = 1;
    /**
     * Set when node has been enqueued for render.
     *
     * This bit may only be toggled by the queue.
     */
    public const STATE_ENQUEUED = 2;
    /**
     * Set when the node has been unmounted.
     *
     * This bit may only be set by the renderer. Once it has been set, the node is discarded and should no longer be used. All operations on unmounted nodes should throw an exception.
     */
    public const STATE_UNMOUNTED = 4;

    /** @var int-mask-of<self::STATE_*> */
    public int $state = self::STATE_INITIAL;

    public readonly int $depth;

    /** @var array<mixed>|null */
    public array | null $nextProps = null;
    /** @var array<mixed>|null */
    public array | null $props = null;

    /** @var array<Hook> */
    public array $hooks = [];

    /** @var list<mixed> */
    public array $children = [];

    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly int $id,
        public readonly Node | null $parent,
        public readonly string | null $key,
        public readonly mixed $type,
        public readonly Closure | null $component,
    ) {
        $this->depth = $parent ? $parent->depth + 1 : 0;
    }
}
