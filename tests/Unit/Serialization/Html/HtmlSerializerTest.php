<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Errors\InvalidAttributeException;
use StefanFisk\PhpReact\Errors\InvalidElementTypeException;
use StefanFisk\PhpReact\Errors\InvalidNodeValueException;
use StefanFisk\PhpReact\Errors\InvalidTagException;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Serialization\Html\HtmlSerializer;
use StefanFisk\PhpReact\Serialization\Html\Middleware\HtmlAttributeValueMiddlewareInterface;
use StefanFisk\PhpReact\Serialization\Html\Middleware\HtmlNodeValueMiddlewareInterface;
use StefanFisk\PhpReact\Support\HtmlString;
use Throwable;
use stdClass;

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

        $node->state = Node::STATE_NONE;

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

        $node->state = Node::STATE_NONE;

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

    /** @return array<string,array{string}> */
    public static function voidElementsProvider(): array
    {
        return [
            'area' => ['area'],
            'base' => ['base'],
            'br' => ['br'],
            'col' => ['col'],
            'embed' => ['embed'],
            'hr' => ['hr'],
            'img' => ['img'],
            'input' => ['input'],
            'link' => ['link'],
            'meta' => ['meta'],
            'source' => ['source'],
            'track' => ['track'],
            'wbr' => ['wbr'],
        ];
    }

    /** @return array<string,array{string}> */
    public static function rawTextElementsProvider(): array
    {
        return [
            'iframe' => ['iframe'],
            'noembed' => ['noembed'],
            'noframes' => ['noframes'],
            'plaintext' => ['plaintext'],
            'script' => ['script'],
            'style' => ['style'],
            'xmp' => ['xmp'],
        ];
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
            '<div foo="&amp;> bar"></div>',
            el('div', ['foo' => '&> bar']),
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

    public function testTrueBoolProp(): void
    {
        $this->assertRenderMatches(
            '<div foo></div>',
            el('div', ['foo' => true]),
        );
    }

    public function testFalseBoolProp(): void
    {
        $this->assertRenderMatches(
            '<div></div>',
            el('div', ['foo' => false]),
        );
    }

    public function testNullProp(): void
    {
        $this->assertRenderMatches(
            '<div></div>',
            el('div', ['foo' => null]),
        );
    }

    public function testIndexedValidStringProp(): void
    {
        $this->assertRenderMatches(
            '<div foo></div>',
            el('div', ['foo']),
        );
    }

    public function testIndexedInvalidStringProp(): void
    {
        $this->assertRenderThrows(
            InvalidAttributeException::class,
            el('div', ['foo>']),
        );
    }

    public function testThrowsForIndexedIntProp(): void
    {
        $this->assertRenderThrows(
            InvalidAttributeException::class,
            el('div', [123]),
        );
    }

    public function testAppliesAttributeMiddlewaresInOrder(): void
    {
        $this->markTestIncomplete();
    }

    public function testThrowsWhenAttributeMiddlewareThrows(): void
    {
        $el = el('div', ['foo' => new stdClass()]);

        $node = $this->elToNode($el);

        $serializer = new HtmlSerializer(
            middlewares: [
                new class implements HtmlAttributeValueMiddlewareInterface {
                    public function processAttributeValue(string $name, mixed $value, Closure $next): mixed
                    {
                        throw new RuntimeException('Middleware failed.');
                    }
                },
            ],
        );

        $this->expectException(InvalidAttributeException::class);

        $serializer->serialize($node);
    }

    public function testThrowsForNonScalarProp(): void
    {
        $this->assertRenderThrows(
            InvalidAttributeException::class,
            el('div', ['foo' => new stdClass()]),
        );
    }

    #[DataProvider('voidElementsProvider')]
    public function testVoidElementsDoNotHaveEndTags(string $tagName): void
    {
        $this->assertRenderMatches(
            "<$tagName foo=\"bar\">",
            el($tagName, ['foo' => 'bar']),
        );
    }

    #[DataProvider('voidElementsProvider')]
    public function testThrowsIfVoidElementsHaveChildren(string $tagName): void
    {
        $this->assertRenderThrows(
            InvalidTagException::class,
            el($tagName, [], 'foo'),
        );
    }

    #[DataProvider('rawTextElementsProvider')]
    public function testDoesThrowsIfRawTextElementsHasScalarChildren(string $tagName): void
    {
        $this->assertRenderThrows(
            InvalidNodeValueException::class,
            el($tagName, [], 'foo'),
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

    public function testAppliesValueMiddlewaresInOrder(): void
    {
        $this->markTestIncomplete();
    }

    public function testThrowsWhenValueMiddlewareThrows(): void
    {
        $el = el('div', [], new stdClass());

        $node = $this->elToNode($el);

        $serializer = new HtmlSerializer(
            middlewares: [
                new class implements HtmlNodeValueMiddlewareInterface {
                    public function processNodeValue(mixed $value, Closure $next): mixed
                    {
                        throw new RuntimeException('Middleware failed.');
                    }
                },
            ],
        );

        $this->expectException(InvalidNodeValueException::class);

        $serializer->serialize($node);
    }
}
