<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Integration;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\BaseElement;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Errors\InvalidAttributeException;
use StefanFisk\Vy\Errors\InvalidTagException;
use StefanFisk\Vy\Errors\RenderException;
use StefanFisk\Vy\Hooks\EffectHook;
use StefanFisk\Vy\Hooks\StateHook;
use StefanFisk\Vy\Serialization\Html\UnsafeHtml;
use StefanFisk\Vy\Tests\Support\FooComponent;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\Vy\Tests\TestCase;
use StefanFisk\Vy\Vy;
use Throwable;

use function StefanFisk\Vy\el;

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
            el('div')('Foo > Bar'),
        );
    }

    public function testDoubleEncodesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>Foo &amp;gt; Bar</div>',
            el('div')('Foo &gt; Bar'),
        );
    }

    public function testConcatenatesTextChildren(): void
    {
        $this->assertRenderMatches(
            '<div>FooBar</div>',
            el('div')('Foo', 'Bar'),
        );
    }

    public function testIgnoresBoolChildren(): void
    {
        $this->assertRenderMatches(
            '<div>FooBar</div>',
            el('div')(true, 'Foo', false, 'Bar', true),
        );
    }

    public function testPreservesChildrenWhitespace(): void
    {
        $this->assertRenderMatches(
            "<div> Foo  \t\n  Bar </div>",
            el('div')(' Foo ', ' ', "\t", "\n", ' ', ' Bar '),
        );
    }

    public function testIntChild(): void
    {
        $this->assertRenderMatches(
            '<div>123</div>',
            el('div')(123),
        );
    }

    public function testFloatChild(): void
    {
        $this->assertRenderMatches(
            '<div>123.456</div>',
            el('div')(123.456),
        );
    }

    public function testElementChild(): void
    {
        $this->assertRenderMatches(
            '<div><div>foo</div></div>',
            el('div')(el('div')('foo')),
        );
    }

    public function testFlattensChildren(): void
    {
        $this->assertRenderMatches(
            '<div><div>foo</div><div>bar</div></div>',
            el('div')([
                [
                    el('div')(['foo']),
                ],
            ], el('div')('bar')),
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
            el('img')('foo'),
        );
    }

    public function testFragmentsRenderChildren(): void
    {
        $this->assertRenderMatches(
            '<div>foo</div>bar<div>baz</div>',
            el()(el('div')('foo'), 'bar', el('div')('baz')),
        );
    }

    public function testFragmentsCannotHaveProps(): void
    {
        $this->assertRenderThrows(
            RenderException::class,
            el('', ['foo' => 'bar'])('baz'),
        );
    }

    public function testRendersUnsafeHtml(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div')(UnsafeHtml::from('<h1 class="unsafe">bar</h1>')),
        );
    }

    public function testRendersClosureUnsafeHtmlReturnValue(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div')(fn () => UnsafeHtml::from('<h1 class="unsafe">bar</h1>')),
        );
    }

    public function testUnsafeHtmlRendersClosureOutput(): void
    {
        $this->assertRenderMatches(
            '<div><h1 class="unsafe">bar</h1></div>',
            el('div')(UnsafeHtml::from(function (): void {
                echo '<h1 class="unsafe">bar</h1>';
            })),
        );
    }

    public function testParentComponent(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
        )(el('div', ['class' => 'children'])($props['children']));

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            el($c, ['foo' => 'bar'])('baz'),
        );
    }

    public function testChildComponent(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
        )(el('div', ['class' => 'children'])($props['children']));

        $this->assertRenderMatches(
            '<div><div data-foo="bar"><div class="children">baz</div></div></div>',
            el('div')(el($c, ['foo' => 'bar'])('baz')),
        );
    }

    public function testNestedComponents(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
        )(el('div', ['class' => 'children'])($props['children']));

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children"><div data-foo="baz"><div class="children">qux</div></div></div></div>', // phpcs:ignore Generic.Files.LineLength.TooLong
            el($c, ['foo' => 'bar'])(el($c, ['foo' => 'baz'])('qux')),
        );
    }

    public function testClosureComponent(): void
    {
        $c = fn (mixed ...$props): mixed => el(
            'div',
            ['data-foo' => $props['foo']],
        )(el('div', ['class' => 'children'])($props['children']));

        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            el($c, ['foo' => 'bar'])('baz'),
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

    public function testNamedParameterComponent(): void
    {
        $this->assertRenderMatches(
            '<div data-foo="bar"><div class="children">baz</div></div>',
            FooComponent::el(foo: 'bar', children: 'baz'),
        );
    }

    public function testSingleLevelContext(): void
    {
        $ctx = Context::create('');

        $c1 = fn (mixed ...$props): mixed => $ctx->el('bar')($props['children']);

        $c2 = function () use ($ctx): mixed {
            $foo = $ctx->use();

            return el('div')($foo);
        };

        $this->assertRenderMatches(
            '<div>bar</div>',
            el($c1)(el($c2)),
        );
    }

    public function testMultiLevelContext(): void
    {
        $ctx = Context::create('foo');

        $c1 = fn (mixed $children = null) => $ctx->el('bar')($children);
        $c2 = fn (mixed $children = null) => $ctx->el('baz')($children);
        $c3 = function () use ($ctx): mixed {
            $foo = $ctx->use();

            return el('div')($foo);
        };

        $this->assertRenderMatches(
            '<div>foo</div><div>bar</div><div>baz</div>',
            el()(
                el($c3),
                el($c1)(
                    el($c3),
                    el($c2)(
                        el($c3),
                    ),
                ),
            ),
        );
    }

    public function testParallellContexts(): void
    {
        $ctx1 = Context::create('');
        $ctx2 = Context::create('');

        $c1 = fn (mixed ...$props): mixed => $ctx1->use();
        $c2 = fn (mixed ...$props): mixed => $ctx2->use();

        $this->assertRenderMatches(
            'ctx1,ctx2',
            $ctx1->el(
                value: 'ctx1',
            )(
                $ctx2->el(
                    value: 'ctx2',
                )(
                    el($c1),
                    ',',
                    el($c2),
                ),
            ),
        );
    }

    public function testModifyingExistingContext(): void
    {
        $ctx = Context::create('');

        $c = function (?string $value = null, mixed $children = null) use ($ctx): mixed {
            $contextValue = $ctx->use();

            return $ctx->el(
                value: $value ?? $contextValue,
            )(
                el('div')(
                    $contextValue,
                ),
                $children,
            );
        };

        $this->assertRenderMatches(
            '<div>bar</div><div>baz</div>',
            $ctx->el(
                value: 'bar',
            )(
                el($c, ['value' => 'baz'])(
                    el($c),
                ),
            ),
        );
    }

    public function testDefaultContextValue(): void
    {
        $ctx = Context::create('foo');

        $c = function () use ($ctx): mixed {
            $foo = $ctx->use();

            return el('div')(
                $foo,
            );
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
        $root = fn ($children) => el('html')($children);

        $vy = new Vy(
            rootComponent: el($root),
        );

        $this->assertEquals(
            '<html><div>child</div></html>',
            $vy->render(el('div')('child')),
        );
    }
}
