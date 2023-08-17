<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

use StefanFisk\PhpReact\Element;
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
use function is_subclass_of;
use function next;
use function reset;

class Renderer implements HookHandlerInterface
{
    /** @var array<Node> */
    private array $renderQueue = [];

    private Node | null $currentNode = null;

    public function __construct(
        private readonly NodeFactory $nodeFactory = new NodeFactory(),
        private readonly Comparator $comparator = new Comparator(),
        private readonly Differ $differ = new Differ(),
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
        $node = $this->createNode(
            parent: null,
            el: $el,
        );

        $this->giveNodeNextProps($node, $el->props);

        $this->processRenderQueue();

        $serialized = $serializer->serialize($node);

        $this->unmount($node);

        return $serialized;
    }

    public function createNode(Node | null $parent, Element $el): Node
    {
        return $this->nodeFactory->createNode(parent: $parent, el: $el);
    }

    /** @param array<mixed> $nextProps */
    public function giveNodeNextProps(Node $node, array $nextProps): void
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

        $newChildren = $this->differ->diffChildren(
            renderer: $this,
            parent: $node,
            oldChildren: $oldChildren,
            renderChildren: $renderChildren,
        );

        $node->children = $newChildren;
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

    public function unmount(Node $node): void
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
