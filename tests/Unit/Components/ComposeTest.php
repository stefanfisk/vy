<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Components;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Components\Compose;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(Compose::class)]
class ComposeTest extends TestCase
{
    /**
     * @param list<Element> $expected
     * @param list<mixed> $elements
     */
    private function assertComposedElementsEquals(array $expected, array $elements): void
    {
        $el123 = Compose::el($elements);

        $this->assertSame(Compose::class, $el123->type);
        $this->assertNull($el123->key);
        $this->assertSame(
            [
                'elements' => $elements,
            ],
            $el123->props,
        );

        $el123Instance = new Compose();

        $el = $el123Instance->render(
            elements: $el123->props['elements'],
            children: 'foo',
        );

        foreach ($expected as $elT) {
            $this->assertInstanceOf(Element::class, $elT);

            $this->assertSame($el->type, $elT->type);
            $this->assertNull($el->key);
            $this->assertCount(1, $el->props);
            $this->assertArrayHasKey('children', $el->props);

            $children = $el->props['children'];

            $this->assertIsList($children);
            $this->assertCount(1, $children);

            $el = $children[0];
        }

        $this->assertSame('foo', $el);
    }

    public function testAppliesElementsInReverseOrder(): void
    {
        $el1 = new Element(type: fn ($children) => $children);
        $el2 = new Element(type: fn ($children) => $children);
        $el3 = new Element(type: fn ($children) => $children);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, $el2, $el3],
        );
    }

    public function testFlattensArray(): void
    {
        $el1 = new Element(type: fn ($children) => $children);
        $el2 = new Element(type: fn ($children) => $children);
        $el3 = new Element(type: fn ($children) => $children);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, [$el2, [$el3]]],
        );
    }

    public function testFiltersNullAndBool(): void
    {
        $el1 = new Element(type: fn ($children) => $children);
        $el2 = new Element(type: fn ($children) => $children);
        $el3 = new Element(type: fn ($children) => $children);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, true, [$el2, false, [$el3, null]]],
        );
    }

    public function testThrowsForInvalidElementTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $el = new Compose();

        $el->render(elements: ['foo']);
    }
}
