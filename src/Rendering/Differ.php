<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Rendering;

use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Errors\RenderException;

use function assert;
use function count;
use function in_array;
use function sprintf;

class Differ
{
    /**
     * @param list<mixed> $oldChildren
     * @param list<mixed> $renderChildren
     *
     * @return list<mixed>
     */
    public function diffChildren(Renderer $renderer, Node $parent, array $oldChildren, array $renderChildren): array
    {
        // Index the old children

        /** @var array<string,Node> $oldKeyToChild */
        $oldKeyToChild = [];
        /** @var list<mixed> $oldIToChild */
        $oldIToChild = [];

        foreach ($oldChildren as $child) {
            if ($child instanceof Node && $child->key) {
                assert(!isset($oldKeyToChild[$child->key]));

                $oldKeyToChild[$child->key] = $child;
            } else {
                $oldIToChild[] = $child;
            }
        }

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

            if ($renderChild instanceof Element && $renderChild->key) {
                $oldChild = $oldKeyToChild[$renderChild->key] ?? null;
            } else {
                $oldChild = $oldIToChild[$renderChildIToChildI[$i]] ?? null;
            }

            $newChild = $this->diffChild(
                renderer: $renderer,
                parent: $parent,
                oldChild: $oldChild,
                renderChild: $renderChild,
            );

            $newChildren[] = $newChild;
        }

        // Unmount new orphans

        foreach ($oldChildren as $oldChild) {
            if (!$oldChild instanceof Node || in_array($oldChild, $newChildren)) {
                continue;
            }

            $renderer->unmount($oldChild);
        }

        // Done

        return $newChildren;
    }

    private function diffChild(Renderer $renderer, Node $parent, mixed $oldChild, mixed $renderChild): mixed
    {
        $newChild = null;

        if (
            $oldChild instanceof Node
            && $renderChild instanceof Element
            && $oldChild->type === $renderChild->type
        ) {
            $newChild = $oldChild;

            $renderer->giveNodeNextProps($newChild, $renderChild->props);
        }

        if (! $newChild) {
            if ($renderChild instanceof Element) {
                $newChild = $renderer->createNode(
                    el: $renderChild,
                    parent: $parent,
                );

                $renderer->giveNodeNextProps($newChild, $renderChild->props);
            } else {
                $newChild = $renderChild;
            }
        }

        return $newChild;
    }
}
