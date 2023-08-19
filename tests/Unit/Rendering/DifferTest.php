<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Rendering;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Rendering\Differ;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksRendererTrait;
use StefanFisk\PhpReact\Tests\TestCase;
use stdClass;

use function StefanFisk\PhpReact\el;

#[CoversClass(Differ::class)]
class DifferTest extends TestCase
{
    use MocksRendererTrait;
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
        $this->differ = new Differ();
    }

    /**
     * @param list<mixed> $newChildren
     * @param list<mixed> $oldChildren
     * @param list<mixed> $renderChildren
     */
    private function assertDiffMatches(array $newChildren, array $oldChildren, array $renderChildren): void
    {
        $this->assertSame(
            $newChildren,
            $this->differ->diffChildren(
                renderer: $this->renderer,
                parent: $this->parent,
                oldChildren: $oldChildren,
                renderChildren: $renderChildren,
            ),
        );
    }

    public function testDoesWhenBothOldAndRenderChildrenAreEmpty(): void
    {
        $this->assertDiffMatches(
            newChildren: [],
            oldChildren: [],
            renderChildren: [],
        );
    }

    public function testReturnsNonNodeItems(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertDiffMatches(
            newChildren: [$value1, $value2],
            oldChildren: [],
            renderChildren: [$value1, $value2],
        );
    }

    public function testRemovesOldNonNodeValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();
        $value3 = new stdClass();

        $this->assertDiffMatches(
            newChildren: [$value1, $value3],
            oldChildren: [$value1, $value2, $value3],
            renderChildren: [$value1, $value3],
        );
    }

    public function testRemovesInsertNewNonNodeValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();
        $value3 = new stdClass();

        $this->assertDiffMatches(
            newChildren: [$value1, $value2, $value3],
            oldChildren: [$value1, $value3],
            renderChildren: [$value1, $value2, $value3],
        );
    }

    public function testCreatesNodesForNewElements(): void
    {
        $props = ['foo' => 'bar', new stdClass(), 'children' => 'baz'];
        $el = el('div', $props);
        $node = $this->createStubNode();

        $this->renderer
            ->shouldReceive('createNode')
            ->once()
            ->with($this->parent, $el)
            ->andReturn($node);

        $this->renderer
            ->shouldReceive('giveNodeNextProps')
            ->once()
            ->with($node, $props);

        $this->assertDiffMatches(
            newChildren: ['foo', $node, 'bar'],
            oldChildren: ['foo', 'bar'],
            renderChildren: ['foo', $el, 'bar'],
        );
    }

    public function testReusesNodesForElementsWithSameTypeAndIndexWhenAllAreIndexed(): void
    {
        $type = new stdClass();
        $props = ['foo' => 'bar', new stdClass(), 'children' => 'baz'];
        $el = el($type, $props);
        $node = $this->createStubNode(
            type: $type,
        );

        $this->renderer
            ->shouldReceive('giveNodeNextProps')
            ->once()
            ->with($node, $props);

        $this->assertDiffMatches(
            newChildren: ['foo', $node, 'bar'],
            oldChildren: ['foo', $node, 'bar'],
            renderChildren: ['foo', $el, 'bar'],
        );
    }

    public function testDoesNotReuseNodesForElementsWithDifferentType(): void
    {
        $type1 = new stdClass();
        $node1 = $this->createStubNode(
            type: $type1,
        );

        $type2 = new stdClass();
        $props = ['foo' => 'bar', new stdClass(), 'children' => 'baz'];
        $el2 = el($type2, $props);
        $node2 = $this->createStubNode(
            type: $type2,
        );

        $this->renderer
            ->shouldReceive('createNode')
            ->once()
            ->with($this->parent, $el2)
            ->andReturn($node2);

        $this->renderer
            ->shouldReceive('giveNodeNextProps')
            ->once()
            ->with($node2, $props);

        $this->renderer
            ->shouldReceive('unmount')
            ->once()
            ->with($node1);

        $this->assertDiffMatches(
            newChildren: ['foo', $node2, 'bar'],
            oldChildren: ['foo', $node1, 'bar'],
            renderChildren: ['foo', $el2, 'bar'],
        );
    }

    public function testReusesNodesForElementsWithSameTypeWhenSomeAreKeyed(): void
    {
        $node1 = $this->createStubNode(key: null, type: 'type1');
        $node2 = $this->createStubNode(key: '2', type: 'type2');
        $node3 = $this->createStubNode(key: null, type: 'type3');

        $props = ['foo' => 'bar', new stdClass(), 'children' => 'baz'];
        $el1 = new Element(key: null, type: 'type1', props: $props);
        $el2 = new Element(key: '2', type: 'type2', props: $props);
        $el3 = new Element(key: null, type: 'type3', props: $props);

        $this->renderer
            ->shouldReceive('giveNodeNextProps')
            ->once()
            ->with($node2, $props);

        $this->renderer
            ->shouldReceive('giveNodeNextProps')
            ->once()
            ->with($node1, $props);

        $this->renderer
            ->shouldReceive('giveNodeNextProps')
            ->once()
            ->with($node3, $props);

        $this->assertDiffMatches(
            newChildren: ['foo', $node2, $node1, $node3, 'bar'],
            oldChildren: ['foo', $node1, $node2, $node3, 'bar'],
            renderChildren:['foo', $el2, $el1, $el3, 'bar'],
        );
    }

    public function testReusesNodesForElementsWithSameKey(): void
    {
        $this->markTestIncomplete();
    }

    public function testThrowsForDuplicateKeys(): void
    {
        $this->markTestIncomplete();
    }
}
