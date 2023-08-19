<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support;

use Closure;
use PHPUnit\Framework\Attributes\Before;
use RuntimeException;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Rendering\Node;

use function array_walk_recursive;
use function assert;
use function class_exists;
use function is_object;
use function is_string;

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
     * @param array<mixed>|null $props
     *
     * Node::$state defaults to STATE_NONE.
     */
    public function createStubNode(
        Node | null $parent = null,
        int | null $depth = null,
        int $state = Node::STATE_NONE,
        string | null $key = null,
        mixed $type = null,
        Closure | null $component = null,
        array | null $props = null,
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
            component: $component,
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
    private function renderToStub(mixed $el, Node | null $parent = null): mixed
    {
        if ($el instanceof Element) {
            $key = $el->key;
            $type = $el->type;
            $props = $el->props;

            $component = null;

            if (is_object($type) || is_string($type) && class_exists($type)) {
                $component = function (mixed ...$props) {
                    throw new RuntimeException('Mock component.');
                };
            }

            $node = $this->createStubNode(
                parent: $parent,
                key: $key,
                type: $type,
                component: $component,
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
