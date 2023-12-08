<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

use function StefanFisk\Vy\el;

#[CoversClass(Element::class)]
class ElementTest extends TestCase
{
    use MocksComponentsTrait;

    /** @param array{key:(string|int|null),type:mixed,props:array<mixed>} $expected */
    private function assertElementEquals(array $expected, Element $actual): void
    {
        $this->assertSame(
            $expected,
            [
                'key' => $actual->key,
                'type' => $actual->type,
                'props' => $actual->props,
            ],
        );
    }

    /**
     * @param list<Element> $expected
     * @param list<mixed> $elements
     */
    private function assertComposedElementsEquals(array $expected, array $elements): void
    {
        $el123 = Element::compose(...$elements);

        $this->assertInstanceOf(Closure::class, $el123->type);
        $this->assertNull($el123->key);
        $this->assertEquals([], $el123->props);

        $c123 = $el123->type;

        $el = $c123('foo');

        foreach ([...$expected, 'foo'] as $elT) {
            if ($el instanceof Element) {
                $this->assertInstanceOf(Element::class, $elT);

                $this->assertSame($el->type, $elT->type);
                $this->assertNull($el->key);
                $this->assertCount(1, $el->props);
                $this->assertArrayHasKey('children', $el->props);

                $children = $el->props['children'];

                $this->assertIsList($children);
                $this->assertCount(1, $children);

                $el = $children[0];
            } else {
                $this->assertSame($elT, $el);
            }
        }
    }

    public function testToChildArrayWrapsSingleItemInArray(): void
    {
        $value = new stdClass();

        $this->assertSame(
            [$value],
            Element::toChildArray($value),
        );
    }

    public function testToChildArrayFlattensArrays(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();
        $value3 = new stdClass();

        $this->assertSame(
            [$value1, $value2, $value3],
            Element::toChildArray([[[$value1, $value2]], $value3]),
        );
    }

    public function testToChildArrayFiltersNullValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, null, $value2]),
        );
    }

    public function testToChildArrayFiltersBoolValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, true, false, $value2]),
        );
    }

    public function testToChildArrayFiltersEmptyStrings(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, '', $value2]),
        );
    }

    public function testComposeAppliesElementsInReverseOrder(): void
    {
        $el1 = new Element(type: fn ($children) => $children);
        $el2 = new Element(type: fn ($children) => $children);
        $el3 = new Element(type: fn ($children) => $children);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, $el2, $el3],
        );
    }

    public function testComposeWrapsClosure(): void
    {
        $c2 = fn ($children) => $children;

        $el1 = new Element(type: fn ($children) => $children);
        $el2 = new Element(type: $c2);
        $el3 = new Element(type: fn ($children) => $children);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, $c2, $el3],
        );
    }

    public function testComposeFlattensArray(): void
    {
        $el1 = new Element(type: fn ($children) => $children);
        $el2 = new Element(type: fn ($children) => $children);
        $el3 = new Element(type: fn ($children) => $children);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, [$el2, [$el3]]],
        );
    }

    public function testComposeFiltersNullAndBool(): void
    {
        $el1 = new Element(type: fn ($children) => $children);
        $el2 = new Element(type: fn ($children) => $children);
        $el3 = new Element(type: fn ($children) => $children);

        $this->assertComposedElementsEquals(
            [$el1, $el2, $el3],
            [$el1, true, [$el2, false, [$el3, null]]],
        );
    }

    public function testInvokeMergesChildrenIntoProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => null,
                'type' => 'div',
                'props' => [
                    'foo' => 'bar',
                    'children' => [['baz', 'qux'], 'quux'],
                ],
            ],
            el('div', ['foo' => 'bar'])(['baz', 'qux'], 'quux'),
        );
    }

    public function testInvokeThrowsIfBothChildrenPropAndChildren(): void
    {
        $this->expectException(InvalidArgumentException::class);

        el('div', ['foo' => 'bar', 'children' => 'baz'])('quz');
    }
}
