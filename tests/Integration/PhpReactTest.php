<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Integration;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Components\Context;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Errors\InvalidAttributeException;
use StefanFisk\PhpReact\Errors\InvalidTagException;
use StefanFisk\PhpReact\Errors\RenderException;
use StefanFisk\PhpReact\Hooks\EffectHook;
use StefanFisk\PhpReact\Hooks\StateHook;
use StefanFisk\PhpReact\PhpReact;
use StefanFisk\PhpReact\Serialization\Html\UnsafeHtml;
use StefanFisk\PhpReact\Tests\Support\FooComponent;
use StefanFisk\PhpReact\Tests\Support\FooContext;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\PhpReact\Tests\TestCase;
use Throwable;

use function StefanFisk\PhpReact\el;

#[CoversClass(PhpReact::class)]
class PhpReactTest extends TestCase
{
    use MocksComponentsTrait;
    use MocksInvokablesTrait;

    private function assertRenderMatches(string $expected, Element $el): void
    {
        $phpReact = new PhpReact();

        $this->assertEquals(
            $expected,
            $phpReact->render($el),
        );
    }

    /** @param class-string<Throwable> $exception */
    private function assertRenderThrows(string $exception, Element $el): void
    {
        $phpReact = new PhpReact();

        $this->expectException($exception);

        $phpReact->render($el);
    }

    public function testInvalidTagName(): void
    {
        $this->assertRenderThrows(
            InvalidTagException::class,
            el('"test"'),
        );
    }

    public function testInvalidAttributeName(): void
    {
        $this->assertRenderThrows(
            InvalidAttributeException::class,
            el('div', ['"foo"' => 'bar']),
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

    public function testIgnoresBoolChildren(): void
    {
        $this->assertRenderMatches(
            '<div>FooBar</div>',
            el('div', [], true, 'Foo', false, 'Bar', true),
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

    public function testFlattensChildren(): void
    {
        $this->assertRenderMatches(
            '<div><div>foo</div><div>bar</div></div>',
            el('div', [], [
                [
                    [
                        el('div', [], [['foo']]),
                    ],
                ],
                el('div', [], 'bar'),
            ]),
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

    public function testIgnoresEmptyClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div></div>',
            el('div', ['class' => '']),
        );
    }

    public function testIgnoresNullClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div></div>',
            el('div', ['class' => [null]]),
        );
    }

    public function testSortsClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            el('div', ['class' => 'foo bar']),
        );
    }

    public function testConditionalClassProp(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            el('div', ['class' => ['foo', 'bar' => true, 'baz' => false]]),
        );
    }

    public function testNestedConditionalClassProp(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            el('div', ['class' => ['foo', ['bar' => true], ['baz' => false]]]),
        );
    }

    public function testSortsConditionalClassPropString(): void
    {
        $this->assertRenderMatches(
            '<div class="bar foo"></div>',
            el('div', ['class' => ['foo bar' => true]]),
        );
    }

    public function testVoidElementsDoNotHaveEndTags(): void
    {
        $this->assertRenderMatches(
            '<img foo="bar">',
            el('img', ['foo' => 'bar']),
        );
    }

    public function testVoidElementsCannotHaveChildren(): void
    {
        $this->assertRenderThrows(
            RenderException::class,
            el('img', [], 'foo'),
        );
    }

    public function testFragmentsRenderChildren(): void
    {
        $this->assertRenderMatches(
            '<div>foo</div>bar<div>baz</div>',
            el('', [], [el('div', [], 'foo'), 'bar', el('div', [], 'baz')]),
        );
    }

    public function testFragmentsCannotHaveProps(): void
    {
        $this->assertRenderThrows(
            RenderException::class,
            el('', ['foo' => 'bar'], ['baz']),
        );
    }

    public function testRendersUnsafeHtml(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div', [], UnsafeHtml::from('<h1 class="unsafe">bar</h1>')),
        );
    }

    public function testRendersClosureUnsafeHtmlReturnValue(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div', [], fn () => UnsafeHtml::from('<h1 class="unsafe">bar</h1>')),
        );
    }

    public function testUnsafeHtmlRendersClosureOutput(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div', [], UnsafeHtml::from(function (): void {
                echo '<h1 class="unsafe">bar</h1>';
            })),
        );
    }

    public function testRootComponent(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
            el('div', ['class' => 'children'], $props['children']),
        );

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            el($c, ['foo' => 'bar'], 'baz'),
        );
    }

    public function testChildComponent(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
            el('div', ['class' => 'children'], $props['children']),
        );

        $this->assertRenderMatches(
            '<div><div data-foo="bar"><div class="children">baz</div></div></div>',
            el('div', [], el($c, ['foo' => 'bar'], 'baz')),
        );
    }

    public function testNestedComponents(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
            el('div', ['class' => 'children'], $props['children']),
        );

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children"><div data-foo="baz"><div class="children">qux</div></div></div></div>', // phpcs:ignore Generic.Files.LineLength.TooLong
            el($c, ['foo' => 'bar'], el($c, ['foo' => 'baz'], 'qux')),
        );
    }

    public function testClosureComponent(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
            el('div', ['class' => 'children'], $props['children']),
        );

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            el($c, ['foo' => 'bar'], 'baz'),
        );
    }

    public function testClosureComponentWithoutArgs(): void
    {
        $c = fn (): mixed => el(
            'div',
            ['data-foo' => 'bar'],
        );

        $this->assertRenderMatches(
            '<div data-foo="bar"></div>',
            el($c),
        );
    }

    public function testClassComponent(): void
    {
        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            el(FooComponent::class, ['foo' => 'bar'], 'baz'),
        );
    }

    public function testObjectComponent(): void
    {
        $c = new class {
            /** {@inheritdoc} */
            public function render(mixed ...$props): mixed
            {
                return el(
                    'div',
                    ['data-foo' => $props['foo']],
                    el('div', ['class' => 'children'], $props['children']),
                );
            }
        };

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            el($c, ['foo' => 'bar'], 'baz'),
        );
    }

    public function testNamedParameterComponent(): void
    {
        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            FooComponent::el(foo: 'bar', children: 'baz'),
        );
    }

    public function testSingleLevelContext(): void
    {
        $c1 = fn (mixed ...$props): mixed => el(FooContext::class, [
            'value' => 'bar',
        ], [
            $props['children'],
        ]);

        $c2 = function (): mixed {
            $foo = FooContext::use();

            return el('div', [], $foo);
        };

        $this->assertRenderMatches(
            '<div>bar</div>',
            el($c1, [], el($c2)),
        );
    }

    public function testMultiLevelContext(): void
    {
        $c1 = fn (mixed ...$props): mixed => el(FooContext::class, [
            'value' => 'bar',
        ], [
            $props['children'],
        ]);

        $c2 = fn (mixed ...$props): mixed => el(FooContext::class, [
            'value' => 'baz',
        ], [
            $props['children'],
        ]);

        $c3 = function (): mixed {
            $foo = FooContext::use();

            return el('div', [], $foo);
        };

        $this->assertRenderMatches(
            '<div>foo</div><div>bar</div><div>baz</div>',
            el('', [], [
                el($c3),
                el($c1, [], [
                    el($c3),
                    el($c2, [], [
                        el($c3),
                    ]),
                ]),
            ]),
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
            el($ctx1::class, [
                'value' => 'ctx1',
            ], [
                el($ctx2::class, [
                    'value' => 'ctx2',
                ], [
                    el($c1),
                    ',',
                    el($c2),
                ]),
            ]),
        );
    }

    public function testModifyingExistingContext(): void
    {
        $c = function (mixed ...$props): mixed {
            $propFoo = $props['value'] ?? null;

            $contextFoo = FooContext::use();

            return el(
                FooContext::class,
                ['value' => $propFoo ?? $contextFoo],
                el('div', [], $contextFoo),
                $props['children'] ?? null,
            );
        };

        $this->assertRenderMatches(
            '<div>bar</div><div>baz</div>',
            el(
                FooContext::class,
                ['value' => 'bar'],
                el($c, ['value' => 'baz'], el($c)),
            ),
        );
    }

    public function testDefaultContextValue(): void
    {
        $c = function (): mixed {
            $foo = FooContext::use();

            return el('div', [], $foo);
        };

        $this->assertRenderMatches(
            '<div>foo</div>',
            el($c),
        );
    }

    public function testSetState(): void
    {
        $c = function (mixed ...$props): mixed {
            [$val, $setVal] = StateHook::use('foo');
            EffectHook::use(fn () => $setVal('bar'), []);

            return $val;
        };

        $this->assertRenderMatches(
            'bar',
            el($c),
        );
    }

    public function testEffect(): void
    {
        $setup = $this->createMockInvokable();
        $setup
            ->shouldReceive('__invoke')
            ->once();

        $c = function (mixed ...$props) use ($setup): mixed {
            [$val, $setVal] = StateHook::use('foo');
            EffectHook::use(fn () => $setVal('bar'), []);
            EffectHook::use($setup->fn, []);

            return $val;
        };

        $this->assertRenderMatches(
            'bar',
            el($c),
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
                return el($inner, ['setVal' => $setVal]);
            }

            return 'outer';
        });
        $outer
            ->shouldReceiveRender()
            ->times(2);

        $this->assertRenderMatches(
            'outer',
            el($outer),
        );
    }
}
