<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Errors\RenderException;
use StefanFisk\PhpReact\Hooks\Hook;
use StefanFisk\PhpReact\Hooks\HookHandlerInterface;
use StefanFisk\PhpReact\Support\Comparator;
use Throwable;

use function assert;
use function current;
use function is_subclass_of;
use function next;
use function reset;

class Renderer implements HookHandlerInterface
{
    private Node | null $currentNode = null;

    public function __construct(
        private readonly NodeFactory $nodeFactory = new NodeFactory(),
        private readonly Comparator $comparator = new Comparator(),
        private readonly Queue $queue = new Queue(),
        private readonly Differ $differ = new Differ(),
    ) {
    }

    public function createNode(Node | null $parent, Element $el): Node
    {
        return $this->nodeFactory->createNode(parent: $parent, el: $el);
    }

    /** @param array<mixed> $nextProps */
    public function giveNodeNextProps(Node $node, array $nextProps): void
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));

        if ($node->props !== null && $this->comparator->valuesAreEqual($node->props, $nextProps)) {
            $node->nextProps = null;

            return;
        }

        $node->nextProps = $nextProps;

        $this->enqueueRender($node);
    }

    public function enqueueRender(Node $node): void
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));

        if ($this->currentNode === $node) {
            return;
        }

        $this->queue->insert($node);
    }

    public function processRenderQueue(): void
    {
        while ($node = $this->queue->poll()) {
            $this->render($node);
        }
    }

    private function needsRender(Node $node): bool
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));

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
        assert(!($node->state & Node::STATE_UNMOUNTED));

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
        assert(!($node->state & Node::STATE_UNMOUNTED));

        foreach ($node->children as $child) {
            if ($child instanceof Node) {
                $this->unmount($child);
            }
        }

        foreach ($node->hooks as $hook) {
            $hook->unmount();
        }

        $this->queue->remove($node);

        $node->state = Node::STATE_UNMOUNTED;
    }
}
