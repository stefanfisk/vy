<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

use Closure;
use StefanFisk\PhpReact\Hooks\Hook;

class Node
{
    public const STATE_NONE = 0;
    public const STATE_INITIAL = 1;
    public const STATE_RENDER_ENQUEUED = 2;
    public const STATE_UNMOUNTED = 4;

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
