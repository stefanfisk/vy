<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Rendering;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\DuplicateKeyException;
use StefanFisk\Vy\Rendering\Comparator;
use StefanFisk\Vy\Rendering\DiffChild;
use StefanFisk\Vy\Rendering\Differ;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\Vy\Tests\Support\DebugObject;
use StefanFisk\Vy\Tests\TestCase;

use function StefanFisk\Vy\el;
use function array_map;

#[CoversClass(Differ::class)]
class DifferTest extends TestCase
{
    use CreatesStubNodesTrait;

    private Node $parent;
    private Differ $differ;

    protected function setUp(): void
    {
        $this->parent = new Node(
            id: 0,
            parent: null,
            key: null,
            type: null,
            component: null,
        );
        $this->differ = new Differ(new Comparator());
    }

    /**
     * @param list<array{mixed,mixed}> $newChildren
     * @param list<Node> $nodesToUnmount
     * @param list<mixed> $oldChildren
     * @param list<mixed> $renderChildren
     */
    private function assertDiffMatches(
        array $oldChildren,
        array $renderChildren,
        array $newChildren,
        array $nodesToUnmount,
    ): void {
        $expected = [
            'newChildren' => array_map(
                fn ($child) => [
                    'oldChild' => $child[0],
                    'renderChild' => $child[1],
                ],
                $newChildren,
            ),
            'nodesToUnmount' => $nodesToUnmount,
        ];

        $result = $this->differ->diffChildren(
            parent: $this->parent,
            oldChildren: $oldChildren,
            renderChildren: $renderChildren,
        );
        $actual = [
            'newChildren' => array_map(
                fn (DiffChild $child) => [
                    'oldChild' => $child->oldChild,
                    'renderChild' => $child->renderChild,
                ],
                $result->newChildren,
            ),
            'nodesToUnmount' => $result->nodesToUnmount,
        ];

        $this->assertSame($expected, $actual);
    }

    public function testEmptyInputs(): void
    {
        $this->assertDiffMatches(
            oldChildren: [],
            renderChildren: [],
            newChildren: [],
            nodesToUnmount: [],
        );
    }

    public function testReturnsNonNodeItems(): void
    {
        $value1 = new DebugObject('1');
        $value2 = new DebugObject('2');

        $this->assertDiffMatches(
            oldChildren: [],
            renderChildren: [$value1, $value2],
            newChildren: [
                [null, $value1],
                [null, $value2],
            ],
            nodesToUnmount: [],
        );
    }

    public function testRemovesOldNonChildValues(): void
    {
        $value1 = new DebugObject('1');
        $value2 = new DebugObject('2');
        $value3 = new DebugObject('3');

        $this->assertDiffMatches(
            oldChildren: [$value1, $value2, $value3],
            renderChildren: [$value1, $value3],
            newChildren: [
                [null, $value1],
                [null, $value3],
            ],
            nodesToUnmount: [],
        );
    }

    public function testRemovesInsertNewNonChildValues(): void
    {
        $value1 = new DebugObject('1');
        $value2 = new DebugObject('2');
        $value3 = new DebugObject('3');

        $this->assertDiffMatches(
            oldChildren: [$value1, $value3],
            renderChildren: [$value1, $value2, $value3],
            newChildren: [
                [null, $value1],
                [null, $value2],
                [null, $value3],
            ],
            nodesToUnmount: [],
        );
    }

    public function testReturnsNullForNewElements(): void
    {
        $el = el('div');

        $this->assertDiffMatches(
            oldChildren: ['foo', 'bar'],
            renderChildren: ['foo', $el, 'bar'],
            newChildren: [
                [null, 'foo'],
                [null, $el],
                [null, 'bar'],
            ],
            nodesToUnmount: [],
        );
    }

    public function testReusesNodesForElementsWithSameTypeAndIndexWhenAllAreIndexed(): void
    {
        $el = el('div');
        $node = $this->createStubNode(
            type: 'div',
        );

        $this->assertDiffMatches(
            oldChildren: ['foo', $node, 'bar'],
            renderChildren: ['foo', $el, 'bar'],
            newChildren: [
                [null, 'foo'],
                [$node, $el],
                [null, 'bar'],
            ],
            nodesToUnmount: [],
        );
    }

    public function testDoesNotReuseNodesForElementsWithDifferentType(): void
    {
        $node1 = $this->createStubNode(
            type: '1',
        );

        $el2 = new Element(type: '2');

        $this->assertDiffMatches(
            oldChildren: ['foo', $node1, 'bar'],
            renderChildren: ['foo', $el2, 'bar'],
            newChildren: [
                [null, 'foo'],
                [null, $el2],
                [null, 'bar'],
            ],
            nodesToUnmount: [$node1],
        );
    }

    public function testReusesNodesForElementsWithSameTypeWhenSomeAreKeyed(): void
    {
        $node1 = $this->createStubNode(type: '1');
        $node2 = $this->createStubNode(type: '2', key: '2');
        $node3 = $this->createStubNode(type: '3');

        $el1 = new Element(type: '1');
        $el2 = new Element(type: '2', key: '2');
        $el3 = new Element(type: '3');

        $this->assertDiffMatches(
            oldChildren: ['foo', $node1, $node2, $node3, 'bar'],
            renderChildren:['foo', $el2, $el1, $el3, 'bar'],
            newChildren: [
                [null, 'foo'],
                [$node2, $el2],
                [$node1, $el1],
                [$node3, $el3],
                [null, 'bar'],
            ],
            nodesToUnmount: [],
        );
    }

    public function testReusesNodesForElementsWithSameKey(): void
    {
        $this->markTestIncomplete();
    }

    public function testThrowsForDuplicateKeys(): void
    {
        $el1 = new Element(type: null, key: 'key');
        $el2 = new Element(type: null, key: 'key');

        $this->expectException(DuplicateKeyException::class);

        $this->differ->diffChildren(
            parent: $this->parent,
            oldChildren: [],
            renderChildren: [$el1, $el2],
        );
    }
}
