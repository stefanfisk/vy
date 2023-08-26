<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\InvalidAttributeException;
use StefanFisk\Vy\Errors\InvalidChildValueException;
use StefanFisk\Vy\Errors\InvalidTagException;
use StefanFisk\Vy\Serialization\Html\HtmlSerializer;
use StefanFisk\Vy\Serialization\Html\Transformers\AttributeValueTransformerInterface;
use StefanFisk\Vy\Serialization\Html\Transformers\ChildValueTransformerInterface;
use StefanFisk\Vy\Serialization\Html\UnsafeHtml;
use StefanFisk\Vy\Tests\Support\CreatesStubNodesTrait;
use StefanFisk\Vy\Tests\TestCase;
use Throwable;
use stdClass;

use function StefanFisk\Vy\el;

#[CoversClass(HtmlSerializer::class)]
class HtmlSerializerTest extends TestCase
{
    use CreatesStubNodesTrait;

    private function assertRenderMatches(string $expected, Element $el): void
    {
        $node = $this->renderToStub($el);

        $serializer = new HtmlSerializer(
            transformers: [],
        );

        $actual = $serializer->serialize($node);

        $this->assertSame($expected, $actual);
    }

    /** @psalm-param class-string<Throwable> $exception */
    private function assertRenderThrows(string $exception, Element $el): void
    {
        $node = $this->renderToStub($el);

        $serializer = new HtmlSerializer(
            transformers: [],
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

    public function testNullChild(): void
    {
        $this->assertRenderMatches(
            '<div>foobar</div>',
            el('div', [], ['foo', null, 'bar']),
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

    public function testThrowsForElementWithEmptyStringType(): void
    {
        $this->assertRenderThrows(
            InvalidTagException::class,
            new Element(null, '', []),
        );
    }

    public function testThrowsForUnknownChildType(): void
    {
        $this->assertRenderThrows(
            InvalidChildValueException::class,
            el('div', [], new stdClass()),
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

    public function testChainsAttributeTransformers(): void
    {
        $this->markTestIncomplete();
    }

    public function testThrowsWhenAttributeTransformerThrows(): void
    {
        $el = el('div', ['foo' => new stdClass()]);

        $node = $this->renderToStub($el);

        $serializer = new HtmlSerializer(
            transformers: [
                new class implements AttributeValueTransformerInterface {
                    public function processAttributeValue(string $name, mixed $value): mixed
                    {
                        throw new RuntimeException('Transformer failed.');
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
            InvalidChildValueException::class,
            el($tagName, [], 'foo'),
        );
    }

    public function testRendersHtmlable(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div', [], UnsafeHtml::from('<h1 class="unsafe">bar</h1>')),
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

    public function testSelfClosesSvgChildren(): void
    {
        $this->assertRenderMatches(
            '<svg width="190" height="160" xmlns="http://www.w3.org/2000/svg"><path d="M 130 60 C 120 80, 180 80, 170 60" stroke="black" fill="transparent" /></svg>', // phpcs:ignore Generic.Files.LineLength.TooLong
            el('svg', [
                'width' => 190,
                'height' => 160,
                'xmlns' => 'http://www.w3.org/2000/svg',
            ], [
                el('path', [
                    'd' => 'M 130 60 C 120 80, 180 80, 170 60',
                    'stroke' => 'black',
                    'fill' => 'transparent',
                ]),
            ]),
        );
    }

    public function testDoesNotSelfCloseSvgForeignObjectChildren(): void
    {
        $this->assertRenderMatches(
            '<svg><foreignObject><div>Foo</div></foreignObject></svg>',
            el('svg', [], [
                el('foreignObject', [], [
                    el('div', [], 'Foo'),
                ]),
            ]),
        );
    }

    public function testAppliesValueTransformersInOrder(): void
    {
        $this->markTestIncomplete();
    }

    public function testThrowsWhenValueTransformerThrows(): void
    {
        $el = el('div', [], new stdClass());

        $node = $this->renderToStub($el);

        $serializer = new HtmlSerializer(
            transformers: [
                new class implements ChildValueTransformerInterface {
                    public function processChildValue(mixed $value): mixed
                    {
                        throw new RuntimeException('Transformer failed.');
                    }
                },
            ],
        );

        $this->expectException(InvalidChildValueException::class);

        $serializer->serialize($node);
    }
}
