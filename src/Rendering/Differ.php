<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\DuplicateKeyException;

use function assert;
use function count;
use function in_array;
use function is_int;
use function is_string;

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

        /** @var array<int,int> $renderChildIToChildI */
        $renderChildIToChildI = [];
        /** @var array<non-empty-string,Element> $keyToRenderChild */
        $keyToRenderChild = [];

        for ($i = 0, $j = 0; $i < count($renderChildren); $i++) {
            $renderChild = $renderChildren[$i];
            $renderChildKey = $this->getKey($parent, $renderChild);

            if ($renderChild instanceof Element && $renderChildKey !== null) {
                if (isset($keyToRenderChild[$renderChildKey])) {
                    throw new DuplicateKeyException(
                        message: $renderChildKey,
                        el1: $keyToRenderChild[$renderChildKey],
                        el2: $renderChild,
                        parentNode: $parent,
                    );
                }

                $keyToRenderChild[$renderChildKey] = $renderChild;
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
            $renderChildKey = $this->getKey($parent, $renderChild);

            if ($renderChildKey !== null) {
                $oldChildCandidate = $oldKeyToChild[$renderChildKey] ?? null;
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
     * @return ?non-empty-string
     *
     * @psalm-assert-if-true Element $renderChild
     * @phpstan-assert-if-true Element $renderChild
     */
    private function getKey(?Node $parent, mixed $renderChild): ?string
    {
        if (!$renderChild instanceof Element) {
            return null;
        }

        $key = $renderChild->props['key'] ?? null;

        if ($key === null) {
            return null;
        }

        if (is_int($key)) {
            $key = (string) $key;
        }

        if (!is_string($key) || $key === '') {
            return null;
        }

        return $key;
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
            $renderChildKey = $this->getKey($parent, $renderChild);

            if ($renderChild instanceof Element && $renderChildKey !== null) {
                if (isset($keyToRenderChild[$renderChildKey])) {
                    throw new DuplicateKeyException(
                        message: $renderChildKey,
                        el1: $keyToRenderChild[$renderChildKey],
                        el2: $renderChild,
                        parentNode: $parent,
                    );
                }

                $keyToRenderChild[$renderChildKey] = $renderChild;
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
