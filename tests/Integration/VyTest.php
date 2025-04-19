<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Integration;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\BaseElement;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Components\Fragment;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\InvalidAttributeException;
use StefanFisk\Vy\Errors\InvalidTagException;
use StefanFisk\Vy\Errors\RenderException;
use StefanFisk\Vy\Hooks\EffectHook;
use StefanFisk\Vy\Hooks\StateHook;
use StefanFisk\Vy\Serialization\Html\UnsafeHtml;
use StefanFisk\Vy\Tests\Support\FooComponent;
use StefanFisk\Vy\Tests\Support\FooContext;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\Vy\Tests\TestCase;
use StefanFisk\Vy\Vy;
use Throwable;

#[CoversClass(Vy::class)]
class VyTest extends TestCase
{
    use MocksComponentsTrait;
    use MocksInvokablesTrait;

    private function assertRenderMatches(string $expected, BaseElement $el): void
    {
        $vy = new Vy();

        $this->assertEquals(
            $expected,
            $vy->render($el),
        );
    }

    /** @param class-string<Throwable> $exception */
    private function assertRenderThrows(string $exception, BaseElement $el): void
    {
        $vy = new Vy();

        $this->expectException($exception);

        $vy->render($el);
    }

    public function testInvalidTagName(): void
    {
        $this->assertRenderThrows(
            InvalidTagException::class,
            Element::create('"test"'),
        );
    }

    public function testInvalidAttributeName(): void
    {
        $this->assertRenderThrows(
            InvalidAttributeException::class,
            Element::create('div', [
                '"foo"' => 'bar',
            ]),
        );
    }

    public function testEncodesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>Foo &gt; Bar</div>',
            Element::create('div')(
                'Foo > Bar',
            ),
        );
    }

    public function testDoubleEncodesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>Foo &amp;gt; Bar</div>',
            Element::create('div')(
                'Foo &gt; Bar',
            ),
        );
    }

    public function testConcatenatesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>FooBar</div>',
            Element::create('div')(
                'Foo',
                'Bar',
            ),
        );
    }

    public function testIgnoresBoolChildren(): void
    {
        $this->assertRenderMatches(
            '<div>FooBar</div>',
            Element::create('div')(
                true,
                'Foo',
                false,
                'Bar',
                true,
            ),
        );
    }

    public function testPreservesChildrenWhitespace(): void
    {
        $this->assertRenderMatches(
            "<div> Foo  \t\n  Bar </div>",
            Element::create('div')(
                ' Foo ',
                ' ',
                "\t",
                "\n",
                ' ',
                ' Bar ',
            ),
        );
    }

    public function testIntChild(): void
    {
        $this->assertRenderMatches(
            '<div>123</div>',
            Element::create('div')(
                123,
            ),
        );
    }

    public function testFloatChild(): void
    {
        $this->assertRenderMatches(
            '<div>123.456</div>',
            Element::create('div')(
                123.456,
            ),
        );
    }

    public function testElementChild(): void
    {
        $this->assertRenderMatches(
            '<div><div>foo</div></div>',
            Element::create('div')(
                Element::create('div')(
                    'foo',
                ),
            ),
        );
    }

    public function testFlattensChildren(): void
    {
        $this->assertRenderMatches(
            '<div><div>foo</div><div>bar</div></div>',
            Element::create('div')([
                [
                    Element::create('div')(
                        [
                            'foo',
                        ],
                    ),
                ],
            ], Element::create('div')(
                'bar',
            )),
        );
    }

    public function testEncodesTextProps(): void
    {
        $this->assertRenderMatches(
            '<div foo="&amp;> bar"></div>',
            Element::create('div', [
                'foo' => '&> bar',
            ]),
        );
    }

    public function testDoubleEncodesTextProps(): void
    {
        $this->assertRenderMatches(
            '<div foo="&amp;gt; Bar"></div>',
            Element::create('div', [
                'foo' => '&gt; Bar',
            ]),
        );
    }

    public function testEncodesTextPropQuotes(): void
    {
        $this->assertRenderMatches(
            '<div foo="&quot;Bar&quot; \'Baz\'"></div>',
            Element::create('div', [
                'foo' => '"Bar" \'Baz\'',
            ]),
        );
    }

    public function testIntProps(): void
    {
        $this->assertRenderMatches(
            '<div foo="123"></div>',
            Element::create('div', [
                'foo' => 123,
            ]),
        );
    }

    public function testFloatProp(): void
    {
        $this->assertRenderMatches(
            '<div foo="123.456"></div>',
            Element::create('div', [
                'foo' => 123.456,
            ]),
        );
    }

    public function testIgnoresEmptyClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div></div>',
            Element::create('div', [
                'class' => '',
            ]),
        );
    }

    public function testIgnoresNullClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div></div>',
            Element::create('div', [
                'class' => [null],
            ]),
        );
    }

    public function testSortsClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            Element::create('div', [
                'class' => 'foo bar',
            ]),
        );
    }

    public function testConditionalClassProp(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            Element::create('div', [
                'class' => [
                    'foo',
                    'bar' => true,
                    'baz' => false,
                ],
            ]),
        );
    }

    public function testNestedConditionalClassProp(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            Element::create('div', [
                'class' => [
                    'foo',
                    [
                        'bar' => true,
                    ],
                    [
                        'baz' => false,
                    ],
                ],
            ]),
        );
    }

    public function testSortsConditionalClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            Element::create('div', [
                'class' => [
                    'foo bar' => true,
                ],
            ]),
        );
    }

    public function testVoidElementsDoNotHaveEndTags(): void
    {
        $this->assertRenderMatches(
            '<img foo="bar">',
            Element::create('img', [
                'foo' => 'bar',
            ]),
        );
    }

    public function testVoidElementsCannotHaveChildren(): void
    {
        $this->assertRenderThrows(
            RenderException::class,
            Element::create('img')(
                'foo',
            ),
        );
    }

    public function testFragmentsRenderChildren(): void
    {
        $this->assertRenderMatches(
            '<div>foo</div>bar<div>baz</div>',
            Fragment::el()(
                Element::create('div')(
                    'foo',
                ),
                'bar',
                Element::create('div')(
                    'baz',
                ),
            ),
        );
    }

    public function testRendersUnsafeHtml(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            Element::create('div')(
                UnsafeHtml::from('<h1 class="unsafe">bar</h1>'),
            ),
        );
    }

    public function testRendersClosureUnsafeHtmlReturnValue(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            Element::create('div')(
                fn () => UnsafeHtml::from('<h1 class="unsafe">bar</h1>'),
            ),
        );
    }

    public function testUnsafeHtmlRendersClosureOutput(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            Element::create('div')(
                UnsafeHtml::from(function (): void {
                    echo '<h1 class="unsafe">bar</h1>';
                }),
            ),
        );
    }

    public function testParentComponent(): void
    {
        $c = fn (mixed ...$props): mixed => Element::create('div', [
            'data-foo' => $props['foo'],
        ])(
            Element::create('div', [
                'class' => 'children',
            ])(
                $props['children'],
            ),
        );

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            Element::create($c, ['foo' => 'bar'])('baz'),
        );
    }

    public function testChildComponent(): void
    {
        $c = fn (mixed ...$props): mixed => Element::create('div', [
            'data-foo' => $props['foo'],
        ])(
            Element::create('div', [
                'class' => 'children',
            ])(
                $props['children'],
            ),
        );

        $this->assertRenderMatches(
            '<div><div data-foo="bar"><div class="children">baz</div></div></div>',
            Element::create('div')(
                Element::create($c, [
                    'foo' => 'bar',
                ])(
                    'baz',
                ),
            ),
        );
    }

    public function testNestedComponents(): void
    {
        $c = fn (mixed ...$props): mixed => Element::create('div', [
            'data-foo' => $props['foo'],
        ])(
            Element::create('div', [
                'class' => 'children',
            ])(
                $props['children'],
            ),
        );

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children"><div data-foo="baz"><div class="children">qux</div></div></div></div>', // phpcs:ignore Generic.Files.LineLength.TooLong
            Element::create($c, [
                'foo' => 'bar',
            ])(
                Element::create($c, [
                    'foo' => 'baz',
                ])(
                    'qux',
                ),
            ),
        );
    }

    public function testClosureComponent(): void
    {
        $c = fn (mixed ...$props): mixed => Element::create('div', [
            'data-foo' => $props['foo'],
        ])(
            Element::create('div', [
                'class' => 'children',
            ])(
                $props['children'],
            ),
        );

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            Element::create($c, [
                'foo' => 'bar',
            ])(
                'baz',
            ),
        );
    }

    public function testClosureComponentWithoutArgs(): void
    {
        $c = fn (): mixed => Element::create('div', [
            'data-foo' => 'bar',
        ]);

        $this->assertRenderMatches(
            '<div data-foo="bar"></div>',
            Element::create($c),
        );
    }

    public function testNamedParameterComponent(): void
    {
        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            FooComponent::el(foo: 'bar')(
                'baz',
            ),
        );
    }

    public function testSingleLevelContext(): void
    {
        $c1 = fn (mixed ...$props): mixed => FooContext::el(
            value: 'bar',
        )(
            $props['children'],
        );

        $c2 = function (): mixed {
            $foo = FooContext::use();

            return Element::create('div')(
                $foo,
            );
        };

        $this->assertRenderMatches(
            '<div>bar</div>',
            Element::create($c1)(
                Element::create($c2),
            ),
        );
    }

    public function testMultiLevelContext(): void
    {
        $c1 = fn (mixed ...$props): mixed => FooContext::el(
            value: 'bar',
        )(
            $props['children'],
        );

        $c2 = fn (mixed ...$props): mixed => FooContext::el(
            value: 'baz',
        )(
            $props['children'],
        );

        $c3 = function (): mixed {
            $foo = FooContext::use();

            return Element::create('div')($foo);
        };

        $this->assertRenderMatches(
            '<div>foo</div><div>bar</div><div>baz</div>',
            Fragment::el()(
                Element::create($c3),
                Element::create($c1)(
                    Element::create($c3),
                    Element::create($c2)(
                        Element::create($c3),
                    ),
                ),
            ),
        );
    }

    public function testParallellContexts(): void
    {
        $ctx1 = new class extends Context {
        };
        $ctx2 = new class extends Context {
        };

        $c1 = fn (mixed ...$props): mixed => $ctx1::use();

        $c2 = fn (mixed ...$props): mixed => $ctx2::use();

        $this->assertRenderMatches(
            'ctx1,ctx2',
            $ctx1::el(
                value: 'ctx1',
            )(
                $ctx2::el(
                    value: 'ctx2',
                )(
                    Element::create($c1),
                    ',',
                    Element::create($c2),
                ),
            ),
        );
    }

    public function testModifyingExistingContext(): void
    {
        $c = function (mixed ...$props): mixed {
            $propFoo = $props['value'] ?? null;

            $contextFoo = FooContext::use();

            return FooContext::el(
                value: $propFoo ?? $contextFoo,
            )(
                Element::create('div')(
                    $contextFoo,
                ),
                $props['children'] ?? null,
            );
        };

        $this->assertRenderMatches(
            '<div>bar</div><div>baz</div>',
            FooContext::el(
                value: 'bar',
            )(
                Element::create($c, [
                    'value' => 'baz',
                ])(
                    Element::create($c),
                ),
            ),
        );
    }

    public function testDefaultContextValue(): void
    {
        $c = function (): mixed {
            $foo = FooContext::use();

            return Element::create('div')($foo);
        };

        $this->assertRenderMatches(
            '<div>foo</div>',
            Element::create($c),
        );
    }

    public function testSetState(): void
    {
        $c = function (mixed ...$props): mixed {
            /** @var string $initialValue */
            $initialValue = 'foo';
            [$val, $setVal] = StateHook::use($initialValue);
            EffectHook::use(fn () => $setVal('bar'), []);

            return $val;
        };

        $this->assertRenderMatches(
            'bar',
            Element::create($c),
        );
    }

    public function testEffect(): void
    {
        $setup = $this->createMockInvokable();
        $setup
            ->shouldReceive('__invoke')
            ->once();

        $c = function (mixed ...$props) use ($setup): mixed {
            /** @var string $initialValue */
            $initialValue = 'foo';
            [$val, $setVal] = StateHook::use($initialValue);
            EffectHook::use(fn () => $setVal('bar'), []);
            EffectHook::use($setup->fn, []);

            return $val;
        };

        $this->assertRenderMatches(
            'bar',
            Element::create($c),
        );
    }

    public function testEffectCleanup(): void
    {
        $cleanup = $this->createMockInvokable();
        $cleanup
            ->shouldReceive('__invoke')
            ->once()
            ->andReturn(null);

        $setup = $this->createMockInvokable();
        $setup
            ->shouldReceive('__invoke')
            ->once()
            ->andReturn($cleanup->fn);

        $inner = $this->createComponentMock(function (Closure $setVal) use ($setup) {
            EffectHook::use(fn () => $setVal('bar'), []);
            EffectHook::use($setup->fn);

            return 'inner';
        });
        $inner
            ->shouldReceiveRender()
            ->times(1);

        $outer = $this->createComponentMock(function () use ($inner) {
            [$val, $setVal] = StateHook::use('foo');

            if ($val === 'foo') {
                return $inner->el(setVal: $setVal);
            }

            return 'outer';
        });
        $outer
            ->shouldReceiveRender()
            ->times(2);

        $this->assertRenderMatches(
            'outer',
            $outer->el(),
        );
    }

    public function testRootComponent(): void
    {
        $root = fn ($children) => Element::create('html')($children);

        $vy = new Vy(
            rootComponent: Element::create($root),
        );

        $this->assertEquals(
            '<html><div>child</div></html>',
            $vy->render(Element::create('div')(
                'child',
            )),
        );
    }
}
