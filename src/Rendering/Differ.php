<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\DuplicateKeyException;

use function assert;
use function count;
use function in_array;

class Differ
{
    /**
     * @param list<mixed> $oldChildren
     * @param list<mixed> $renderChildren
     */
    public function diffChildren(Node $parent, array $oldChildren, array $renderChildren): Diff
    {
        if (!$oldChildren) {
            return $this->createInitialChildren(
                parent: $parent,
                renderChildren: $renderChildren,
            );
        }

        // Index the old children

        /** @var list<Node> $oldChildNodes */
        $oldChildNodes = [];
        /** @var array<string,Node> $oldKeyToChild */
        $oldKeyToChild = [];
        /** @var list<mixed> $oldIToChild */
        $oldIToChild = [];

        foreach ($oldChildren as $child) {
            if ($child instanceof Node) {
                $oldChildNodes[] = $child;
                if ($child->key !== null) {
                    assert(!isset($oldKeyToChild[$child->key]));

                    $oldKeyToChild[$child->key] = $child;
                } else {
                    $oldIToChild[] = $child;
                }
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

            if ($renderChild instanceof Element && $renderChild->key !== null) {
                if (isset($keyToRenderChild[$renderChild->key])) {
                    throw new DuplicateKeyException(
                        message: $renderChild->key,
                        el1: $keyToRenderChild[$renderChild->key],
                        el2: $renderChild,
                        parentNode: $parent,
                    );
                }

                $keyToRenderChild[$renderChild->key] = $renderChild;
            } else {
                $renderChildIToChildI[$i] = $j++;
            }
        }

        // Diff the children

        /** @var list<Node> $newChildNodes */
        $newChildNodes = [];
        /** @var list<DiffChild> $newChildren */
        $newChildren = [];

        foreach ($renderChildren as $i => $renderChild) {
            $oldChildCandidate = null;

            if ($renderChild instanceof Element && $renderChild->key !== null) {
                $oldChildCandidate = $oldKeyToChild[$renderChild->key] ?? null;
            } else {
                $oldChildCandidate = $oldIToChild[$renderChildIToChildI[$i]] ?? null;
            }

            $oldChild = null;

            if (
                $renderChild instanceof Element
                && $oldChildCandidate instanceof Node
                && $oldChildCandidate->type === $renderChild->type
            ) {
                $oldChild = $oldChildCandidate;

                $newChildNodes[] = $oldChild;
            }

            $newChild = new DiffChild(
                oldChild: $oldChild,
                renderChild: $renderChild,
            );

            $newChildren[] = $newChild;
        }

        // Nodes to unmount

        $nodesToUnmount = [];

        foreach ($oldChildNodes as $oldChild) {
            if (in_array($oldChild, $newChildNodes)) {
                continue;
            }

            $nodesToUnmount[] = $oldChild;
        }

        // Done

        return new Diff(
            newChildren: $newChildren,
            nodesToUnmount: $nodesToUnmount,
        );
    }

    /**
     * @param list<mixed> $renderChildren
     */
    private function createInitialChildren(Node $parent, array $renderChildren): Diff
    {
        /** array<string,Element> $keyToRenderChild */
        $keyToRenderChild = [];

        $newChildren = [];

        foreach ($renderChildren as $renderChild) {
            if ($renderChild instanceof Element && $renderChild->key !== null) {
                if (isset($keyToRenderChild[$renderChild->key])) {
                    throw new DuplicateKeyException(
                        message: $renderChild->key,
                        el1: $keyToRenderChild[$renderChild->key],
                        el2: $renderChild,
                        parentNode: $parent,
                    );
                }

                $keyToRenderChild[$renderChild->key] = $renderChild;
            }

            $newChildren[] = new DiffChild(
                oldChild: null,
                renderChild: $renderChild,
            );
        }

        return new Diff(
            newChildren: $newChildren,
            nodesToUnmount: [],
        );
    }
}
