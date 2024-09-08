<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Components;

use Closure;
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
     * @param array<mixed> $elements
     */
    private function assertComposedElementsEquals(array $expected, array $elements): void
    {
        // @phpstan-ignore argument.type
        $el123 = Compose::el($elements);

        $this->assertInstanceOf(Closure::class, $el123->type);
        $this->assertSame(['elements' => $elements], $el123->props);

        $el = ($el123->type)([
            ...$el123->props,
            'children' => 'foo',
        ]);

        foreach ($expected as $elT) {
            $this->assertInstanceOf(Element::class, $elT);

            $this->assertSame($el->type, $elT->type);
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
        $el1 = new Element(fn ($children) => $children, []);
        $el2 = new Element(fn ($children) => $children, []);
        $el3 = new Element(fn ($children) => $children, []);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, $el2, $el3],
        );
    }

    public function testFiltersNullAndBool(): void
    {
        $el1 = new Element(fn ($children) => $children, []);
        $el2 = new Element(fn ($children) => $children, []);
        $el3 = new Element(fn ($children) => $children, []);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, true, $el2, false, $el3, null],
        );
    }

    public function testThrowsForInvalidElementTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore argument.type
        $el = Compose::el(['foo']);

        $this->assertInstanceOf(Closure::class, $el->type);

        ($el->type)($el->props);
    }
}
