<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Errors\InvalidAttributeException;
use StefanFisk\PhpReact\Errors\InvalidElementTypeException;
use StefanFisk\PhpReact\Errors\InvalidTagException;
use StefanFisk\PhpReact\Errors\RenderException;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Serialization\Html\HtmlSerializer;
use StefanFisk\PhpReact\Support\HtmlString;
use Throwable;

use function StefanFisk\PhpReact\el;
use function array_walk_recursive;
use function assert;
use function class_exists;
use function gettype;
use function is_array;
use function is_bool;
use function is_null;
use function is_object;
use function is_string;

use const LIBXML_HTML_NODEFDTD;

/**
 * @covers StefanFisk\PhpReact\Serialization\Html\HtmlSerializer
 */
class HtmlSerializerTest extends TestCase
{
    private int $nextNodeId = 0;

     /**
      * @param T $el
      *
      * @template T of mixed
      * @psalm-return (
      *     T is Element
      *     ? Node
      *     : mixed
      * )
      */
    private function elToNode(mixed $el, Node | null $parent = null): mixed
    {
        if ($el instanceof Element) {
            $type = $el->type;

            if (is_object($type) || is_string($type) && class_exists($type)) {
                return $this->elToComponentNode($el, $parent);
            }

            if (!is_string($type) || $type === '') {
                throw new InvalidElementTypeException(
                    message: gettype($type),
                    el: $el,
                    parentNode: $parent,
                );
            }

            return $this->elToTagNode($el, $parent);
        } else {
            return $el;
        }
    }

    /** @return list<mixed> */
    private function elChildrenToNodes(mixed $elChildren, Node | null $parent): array
    {
        if (!is_array($elChildren)) {
            $elChildren = [$elChildren];
        }

        $children = [];

        array_walk_recursive(
            $elChildren,
            function ($child) use (&$children, $parent) {
                if (is_bool($child) || is_null($child)) {
                    return;
                }

                $children[] = $this->elToNode($child, $parent);
            },
        );

        return $children;
    }

    private function elToTagNode(Element $el, Node | null $parent): Node
    {
        $key = $el->key;
        $type = $el->type;
        $props = $el->props;

        assert(is_string($type) && $type !== '');

        $node = new Node(
            id: $this->nextNodeId++,
            parent: $parent,
            key: $key,
            type: $type,
            component: null,
        );

        $children = $this->elChildrenToNodes($props['children'] ?? [], $node);

        unset($props['children']);
        $node->props = $props;

        $node->children = $children;

        return $node;
    }

    private function elToComponentNode(Element $el, Node | null $parent): Node
    {
        $key = $el->key;
        $type = $el->type;
        $props = $el->props;

        $node = new Node(
            id: $this->nextNodeId++,
            parent: $parent,
            key: $key,
            type: $type,
            component: function (mixed ...$props) {
                throw new RuntimeException('Mock component.');
            },
        );

        $node->props = $props;

        $node->children = $this->elChildrenToNodes($props['children'] ?? [], $node);

        return $node;
    }

    private function assertRenderMatches(string $expected, Element $el): void
    {
        $node = $this->elToNode($el);

        $serializer = new HtmlSerializer(
            middlewares: [],
        );

        $actual = $serializer->serialize($node);

        $expected = $this->normalizeHtml($expected);
        $actual = $this->normalizeHtml($actual);

        $this->assertSame($expected, $actual);
    }

    /** @psalm-param class-string<Throwable> $exception */
    private function assertRenderThrows(string $exception, Element $el): void
    {
        $node = $this->elToNode($el);

        $serializer = new HtmlSerializer(
            middlewares: [],
        );

        $this->expectException($exception);

        $serializer->serialize($node);
    }

    private function normalizeHtml(string $html): string
    {
        static $id = 'e442f1ef43914400a38c';

        $doc = new DOMDocument();

        @$doc->loadHTML('<div id="' . $id . '">' . $html . '</div>', LIBXML_HTML_NODEFDTD); // to ignore HTML5 errors

        $normalizedHtml = '';

        foreach ($doc->getElementById($id)->childNodes ?? [] as $child) {
            $normalizedHtml .= $doc->saveHTML($child);
        }

        return $normalizedHtml;
    }

    public function testThrowForInvalidTagName(): void
    {
        $this->assertRenderthrows(
            InvalidTagException::class,
            el('"test"'),
        );
    }

    public function testInvalidAttributeName(): void
    {
        $this->assertRenderthrows(
            InvalidAttributeException::class,
            el('div', [
                '"foo"' => 'bar',
            ]),
        );
    }

    public function testEncodesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>Foo &gt; Bar</div>',
            el('div', [], 'Foo > Bar'),
        );
    }

    public function testDoubleEncodesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>Foo &amp;gt; Bar</div>',
            el('div', [], 'Foo &gt; Bar'),
        );
    }

    public function testConcatenatesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>FooBar</div>',
            el('div', [], 'Foo', 'Bar'),
        );
    }

    public function testPreservesChildrenWhitespace(): void
    {
        $this->assertRenderMatches(
            "<div> Foo  \t\n  Bar </div>",
            el('div', [], ' Foo ', ' ', "\t", "\n", ' ', ' Bar '),
        );
    }

    public function testIntChild(): void
    {
        $this->assertRenderMatches(
            '<div>123</div>',
            el('div', [], 123),
        );
    }

    public function testFloatChild(): void
    {
        $this->assertRenderMatches(
            '<div>123.456</div>',
            el('div', [], 123.456),
        );
    }

    public function testElementChild(): void
    {
        $this->assertRenderMatches(
            '<div><div>foo</div></div>',
            el('div', [], el('div', [], ['foo'])),
        );
    }

    public function testEncodesTextProps(): void
    {
        $this->assertRenderMatches(
            '<div foo="&gt; bar"></div>',
            el('div', ['foo' => '> bar']),
        );
    }

    public function testDoubleEncodesTextProps(): void
    {
        $this->assertRenderMatches(
            '<div foo="&amp;gt; Bar"></div>',
            el('div', ['foo' => '&gt; Bar']),
        );
    }

    public function testEncodesTextPropQuotes(): void
    {
        $this->assertRenderMatches(
            '<div foo="&quot;Bar&quot; \'Baz\'"></div>',
            el('div', ['foo' => '"Bar" \'Baz\'']),
        );
    }

    public function testIntProps(): void
    {
        $this->assertRenderMatches(
            '<div foo="123"></div>',
            el('div', ['foo' => 123]),
        );
    }

    public function testFloatProp(): void
    {
        $this->assertRenderMatches(
            '<div foo="123.456"></div>',
            el('div', ['foo' => 123.456]),
        );
    }

    public function testVoidElementsDoNotHaveEndTags(): void
    {
        $this->assertRenderMatches(
            '<img foo="bar">',
            el('img', ['foo' => 'bar']),
        );
    }

    public function testThrowsIfVoidElementsHaveChildren(): void
    {
        $this->assertRenderThrows(
            RenderException::class,
            el('img', [], 'foo'),
        );
    }

    public function testRendersHtmlable(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div', [], HtmlString::from('<h1 class="unsafe">bar</h1>')),
        );
    }

    public function testRendersComponentChildren(): void
    {
        $this->assertRenderMatches(
            '<div><div baz="qux"></div>quux</div>',
            el('div', [], [
                el(fn () => null, ['foo' => 'bar'], [
                    el('div', ['baz' => 'qux']),
                    'quux',
                ]),
            ]),
        );
    }

    public function testAppliesMiddlewaresInOrder(): void
    {
        $this->markTestIncomplete();
    }
}
