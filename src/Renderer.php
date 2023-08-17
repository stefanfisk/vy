<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact;

use StefanFisk\PhpReact\Errors\RenderException;
use StefanFisk\PhpReact\Hooks\Hook;
use StefanFisk\PhpReact\Hooks\HookHandlerInterface;
use StefanFisk\PhpReact\Serialization\SerializerInterface;
use StefanFisk\PhpReact\Support\Comparator;
use Throwable;

use function array_filter;
use function array_splice;
use function assert;
use function count;
use function current;
use function in_array;
use function is_subclass_of;
use function next;
use function reset;
use function sprintf;

class Renderer implements HookHandlerInterface
{
    /** @var array<Node> */
    private array $renderQueue = [];

    private Node | null $currentNode = null;

    public function __construct(
        private readonly NodeFactory $nodeFactory = new NodeFactory(),
        private readonly Comparator $comparator = new Comparator(),
    ) {
    }

    /**
     * @param SerializerInterface<T> $serializer
     *
     * @return T
     *
     * @template T
     */
    public function renderAndSerialize(
        Element $el,
        SerializerInterface $serializer,
    ): mixed {
        $node = $this->nodeFactory->createNode(
            el: $el,
            parent: null,
        );

        $this->giveNodeNextProps($node, $el->props);

        $this->processRenderQueue();

        $serialized = $serializer->serialize($node);

        $this->unmount($node);

        return $serialized;
    }

    /** @param array<mixed> $nextProps */
    private function giveNodeNextProps(Node $node, array $nextProps): void
    {
        if ($node->props !== null && $this->comparator->valuesAreEqual($node->props, $nextProps)) {
            $node->nextProps = null;

            return;
        }

        $node->nextProps = $nextProps;

        $this->enqueueRender($node);
    }

    public function enqueueRender(Node $node): void
    {
        if ($node->state & Node::STATE_RENDER_ENQUEUED) {
            return;
        }

        if ($this->currentNode === $node) {
            return;
        }

        $this->renderQueue[] = $node;
        $node->state |= Node::STATE_RENDER_ENQUEUED;
    }

    private function processRenderQueue(): void
    {
        while ($node = $this->getNextNodeInRenderQueue()) {
            $node->state &= ~Node::STATE_RENDER_ENQUEUED;

            $this->render($node);
        }
    }

    private function getNextNodeInRenderQueue(): Node | null
    {
        if (!$this->renderQueue) {
            return null;
        }

        // Find the first node with the lowest depth and render it

        $count = count($this->renderQueue);
        $i = 0;
        $a = $this->renderQueue[$i];

        for ($j = 1; $j < $count; $j++) {
            $b = $this->renderQueue[$j];

            if ($a->depth <= $b->depth) {
                continue;
            }

            $i = $j;
            $a = $b;
        }

        array_splice($this->renderQueue, $i, 1);

        return $a;
    }

    private function needsRender(Node $node): bool
    {
        if ($node->state & Node::STATE_INITIAL) {
            return true;
        }

        if ($node->nextProps !== null) {
            return true;
        }

        foreach ($node->hooks as $hook) {
            if ($hook->needsRender()) {
                return true;
            }
        }

        return false;
    }

    private function render(Node $node): void
    {
        if (!$this->needsRender($node)) {
            return;
        }

        if ($node->nextProps !== null) {
            $node->props = $node->nextProps;
            $node->nextProps = null;
        }
        assert($node->props !== null);

        $oldChildren = $node->children;
        $renderChildren = null;

        $component = $node->component ?: fn (mixed ...$props): mixed => $props['children'] ?? null;

        try {
            Hook::pushHandler($this);
            $this->currentNode = $node;

            $i = 0;
            $maxRenderCount = 30;

            while (true) {
                $i++;
                if ($i > $maxRenderCount) {
                    throw new RenderException(
                        message: 'Too many re-renders',
                        node: $node,
                    );
                }

                reset($node->hooks);

                $renderChildren = $component(...$node->props);

                $node->state &= ~Node::STATE_INITIAL;

                if ($this->needsRender($node)) {
                    continue;
                }

                foreach ($node->hooks as $hook) {
                    $hook->afterRender();
                }

                // @phpstan-ignore-next-line
                if ($this->needsRender($node)) {
                    continue;
                }

                break;
            }
        } catch (RenderException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RenderException(
                message: $e->getMessage(),
                node: $node,
                previous: $e,
            );
        } finally {
            $this->currentNode = null;
            $poppedHandler = Hook::popHandler();
            assert($poppedHandler === $this);
        }

        $node->children = $this->renderChildren(
            parent: $node,
            oldChildren: $oldChildren,
            renderChildren: $renderChildren,
        );
    }

    /** @param class-string<Hook> $class */
    public function useHook(string $class, mixed ...$args): mixed
    {
        assert(is_subclass_of($class, Hook::class));

        $node = $this->currentNode;

        if (!$node) {
            throw new RenderException(
                message: 'Cannot call hooks outside of component render.',
            );
        }

        if ($node->state & Node::STATE_INITIAL) {
            /** @psalm-suppress UnsafeInstantiation */
            $hook = new $class($this, $node, ...$args);

            $node->hooks[] = $hook;

            return $hook->initialRender(...$args);
        } else {
            $hook = current($node->hooks);
            next($node->hooks);

            if (!$hook instanceof $class) {
                throw new RenderException(
                    message: 'Hooks must be called in exact same order on every render.',
                    node: $node,
                );
            }

            return $hook->rerender(...$args);
        }
    }

    /**
     * @param list<mixed> $oldChildren
     *
     * @return list<mixed>
     */
    private function renderChildren(Node $parent, array $oldChildren, mixed $renderChildren): array
    {
        // Index the old children

        /** @var array<string,Node> $oldKeyToChild */
        $oldKeyToChild = [];
        /** @var list<mixed> $oldIToChild */
        $oldIToChild = [];

        foreach ($oldChildren as $child) {
            if ($child instanceof Node && $child->key) {
                $oldKeyToChild[$child->key] = $child;
            } else {
                $oldIToChild[] = $child;
            }
        }

        // Flatten the render children and remove empty items

        $renderChildren = Element::toChildArray($renderChildren);

        // Index the render children

        /** array<int,int> $renderChildIToChildI */
        $renderChildIToChildI = [];
        /** array<string,Element> $keyToRenderChild */
        $keyToRenderChild = [];

        for ($i = 0, $j = 0; $i < count($renderChildren); $i++) {
            $renderChild = $renderChildren[$i];

            if ($renderChild instanceof Element && $renderChild->key) {
                if (isset($keyToRenderChild[$renderChild->key])) {
                    throw new RenderException(
                        message: sprintf('Duplicate key "%s".', $renderChild->key),
                        node: $parent,
                        el: $renderChild,
                    );
                }

                $keyToRenderChild[$renderChild->key] = $renderChild;
            } else {
                $renderChildIToChildI[$i] = $j++;
            }
        }

        // Diff the children

        /** @var list<mixed> $newChildren */
        $newChildren = [];

        foreach ($renderChildren as $i => $renderChild) {
            $oldChild = null;
            $newChild = null;

            if ($renderChild instanceof Element && $renderChild->key) {
                $oldChild = $oldKeyToChild[$renderChild->key] ?? null;
            }

            if (! $oldChild) {
                $oldChild = $oldIToChild[$renderChildIToChildI[$i]] ?? null;
            }

            if (
                $oldChild instanceof Node
                && $renderChild instanceof Element
                && $oldChild->type === $renderChild->type
            ) {
                $newChild = $oldChild;

                $this->giveNodeNextProps($newChild, $renderChild->props);
            }

            if (! $newChild) {
                if ($renderChild instanceof Element) {
                    $newChild = $this->nodeFactory->createNode(
                        el: $renderChild,
                        parent: $parent,
                    );

                    $this->giveNodeNextProps($newChild, $renderChild->props);
                } else {
                    $newChild = $renderChild;
                }
            }

            $newChildren[] = $newChild;
        }

        // Unmount new orphans

        foreach ($oldChildren as $oldChild) {
            if (in_array($oldChild, $newChildren)) {
                continue;
            }

            if ($oldChild instanceof Node) {
                $this->unmount($oldChild);
            }
        }

        // Done

        return $newChildren;
    }

    private function unmount(Node $node): void
    {
        foreach ($node->children as $child) {
            if ($child instanceof Node) {
                $this->unmount($child);
            }
        }

        $node->state = Node::STATE_UNMOUNTED;

        foreach ($node->hooks as $hook) {
            $hook->unmount();
        }

        $this->renderQueue = array_filter($this->renderQueue, static fn ($n) => $n === $node);
    }
}
