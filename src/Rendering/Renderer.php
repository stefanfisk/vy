<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use Closure;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\RenderException;
use StefanFisk\Vy\Hooks\Hook;
use StefanFisk\Vy\Hooks\HookHandlerInterface;
use Throwable;

use function assert;
use function current;
use function is_string;
use function is_subclass_of;
use function next;
use function reset;

class Renderer implements HookHandlerInterface
{
    private int $nextNodeId = 1;

    private ?Node $currentNode = null;

    private readonly Differ $differ;

    public function __construct(
        private readonly Comparator $comparator = new Comparator(),
        private readonly Queue $queue = new Queue(),
        ?Differ $differ = null,
    ) {
        $this->differ = $differ ?? new Differ($comparator);
    }

    public function createNode(?Node $parent, Element $el): Node
    {
        $node = new Node(
            id: $this->nextNodeId++,
            parent: $parent,
            key: $el->key,
            type: $el->type,
        );

        $node->nextProps = $el->props;

        return $node;
    }

    public function valuesAreEqual(mixed $a, mixed $b): bool
    {
        return $this->comparator->valuesAreEqual($a, $b);
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

        $renderChildren = match (true) {
            is_string($node->type) => $this->renderTag($node),
            $node->type instanceof Closure => $this->renderComponent($node),
        };

        if (!$node->children) {
            $this->createInitialChildren(
                node: $node,
                renderChildren: $renderChildren,
            );
        } else {
            $this->diffChildren(
                node: $node,
                renderChildren: $renderChildren,
            );
        }

        foreach ($node->children as $child) {
            if (!$child instanceof Node) {
                continue;
            }

            $this->render($child);
        }
    }

    private function renderTag(Node $node): mixed
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));
        assert(is_string($node->type));

        if ($node->nextProps !== null) {
            $node->props = $node->nextProps;
            $node->nextProps = null;
        }
        assert($node->props !== null);

        // @phpstan-ignore assign.propertyType
        $node->state &= ~Node::STATE_INITIAL;

        return $node->props['children'] ?? null;
    }

    private function renderComponent(Node $node): mixed
    {
        assert(!($node->state & Node::STATE_UNMOUNTED));
        assert($node->type instanceof Closure);

        if ($node->nextProps !== null) {
            $node->props = $node->nextProps;
            $node->nextProps = null;
        }
        assert($node->props !== null);

        $renderChildren = null;

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

                $renderChildren = ($node->type)(...$node->props);

                // @phpstan-ignore assign.propertyType
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

        return $renderChildren;
    }

    private function createInitialChildren(Node $node, mixed $renderChildren): void
    {
        $renderChildren = Element::toChildArray($renderChildren);

        $newChildren = [];

        foreach ($renderChildren as $renderChild) {
            if ($renderChild instanceof Element) {
                $newChild = $this->createNode(
                    parent: $node,
                    el: $renderChild,
                );
            } else {
                $newChild = $renderChild;
            }

            $newChildren[] = $newChild;
        }

        $node->children = $newChildren;
    }

    private function diffChildren(Node $node, mixed $renderChildren): void
    {
        $renderChildren = Element::toChildArray($renderChildren);

        $diff = $this->differ->diffChildren(
            parent: $node,
            oldChildren: $node->children,
            renderChildren: $renderChildren,
        );

        $newChildren = [];

        foreach ($diff->newChildren as $diffChild) {
            $renderChild = $diffChild->renderChild;
            $newChild = $diffChild->oldChild;

            if (!$newChild) {
                if ($renderChild instanceof Element) {
                    $newChild = $this->createNode(
                        parent: $node,
                        el: $renderChild,
                    );
                } else {
                    $newChild = $renderChild;
                }
            } else {
                assert($renderChild instanceof Element);

                $this->giveNodeNextProps(
                    node: $newChild,
                    nextProps: $renderChild->props,
                );
            }

            $newChildren[] = $newChild;
        }

        $node->children = $newChildren;

        foreach ($diff->nodesToUnmount as $oldChild) {
            $this->unmount($oldChild);
        }
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
