<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support;

use Closure;
use PHPUnit\Framework\Attributes\Before;
use RuntimeException;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Rendering\Node;

use function array_walk_recursive;
use function assert;

trait CreatesStubNodesTrait
{
    private int $nextNodeId;

    #[Before]
    protected function setUpCreatesStubNodesTrait(): void
    {
        $this->nextNodeId = 0;
    }

    /**
     * Creates a new node.
     *
     * @param Node::STATE_NONE|Node::STATE_INITIAL|Node::STATE_ENQUEUED|Node::STATE_UNMOUNTED $state
     * @param ?non-empty-string $key
     * @param ?array<mixed> $props
     *
     * Node::$state defaults to STATE_NONE.
     */
    public function createStubNode(
        ?Node $parent = null,
        ?int $depth = null,
        int $state = Node::STATE_NONE,
        ?string $key = null,
        string | Closure $type = '',
        ?array $props = null,
    ): Node {
        assert($parent === null || $depth === null);

        if ($depth > 0) {
            $parent = $this->createStubNode(
                depth: $depth - 1,
            );
        }

        $node = new Node(
            id: $this->nextNodeId++,
            parent: $parent,
            key: $key,
            type: $type,
        );

        $node->state = $state;

        $node->props = $props;

        return $node;
    }

    /**
     * Recursively maps render children to nodes.
     *
     * Component nodes
     *
     * @param T $el
     *
     * @template T of mixed
     * @psalm-return (
     *     T is Element
     *     ? Node
     *     : mixed
     * )
     */
    private function renderToStub(mixed $el, ?Node $parent = null): mixed
    {
        if ($el instanceof Element) {
            $key = $el->key;
            $type = $el->type;
            $props = $el->props;

            if ($type instanceof Closure) {
                $type = function (mixed ...$props) {
                    throw new RuntimeException('Mock component.');
                };
            }

            $node = $this->createStubNode(
                parent: $parent,
                key: $key,
                type: $type,
                props: $props,
            );

            $renderChildren = (array) $props['children'];

             // Wrap the array to make psalm happy
            $wrapper = new class {
                /** @var list<mixed> */
                public array $children = [];
            };

            array_walk_recursive(
                $renderChildren,
                function (mixed $renderChild) use ($node, $wrapper): void {
                    $wrapper->children[] = $this->renderToStub($renderChild, $node);
                },
            );

            $node->children = $wrapper->children;

            return $node;
        } else {
            return $el;
        }
    }
}
