<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Support\Htmlable;
use Stringable;
use UnexpectedValueException;
use stdClass;

use function ob_get_level;
use function ob_start;

#[CoversClass(Htmlable::class)]
class HtmlableTest extends TestCase
{
    public function testFromReturnsInstance(): void
    {
        $this->assertEquals(
            new Htmlable('<div>Foo</div>'),
            Htmlable::from('<div>Foo</div>'),
        );
    }

    public function testToHtmlReturnsString(): void
    {
        $this->assertSame(
            '<div>Foo</div>',
            Htmlable::from('<div>Foo</div>')->toHtml(),
        );
    }

    public function testToHtmlReturnsIntReturnValue(): void
    {
        $this->assertSame(
            '123',
            Htmlable::from(fn () => 123)->toHtml(),
        );
    }

    public function testToHtmlReturnsFloatReturnValue(): void
    {
        $this->assertSame(
            '123.456',
            Htmlable::from(fn () => 123.456)->toHtml(),
        );
    }

    public function testToHtmlReturnsStringReturnValue(): void
    {
        $this->assertSame(
            '<div>Foo</div>',
            Htmlable::from(fn () => '<div>Foo</div>')->toHtml(),
        );
    }

    public function testToHtmlReturnsStringableReturnValue(): void
    {
        $value = new class implements Stringable {
            public function __toString(): string
            {
                return '<div>Foo</div>';
            }
        };

        $this->assertSame(
            '<div>Foo</div>',
            Htmlable::from(fn () => $value)->toHtml(),
        );
    }

    public function testToHtmlThrowsForBoolReturnValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        Htmlable::from(fn () => true)->toHtml();
    }

    public function testToHtmlThrowsForNonStringableObjectReturnValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        Htmlable::from(fn () => new stdClass())->toHtml();
    }

    public function testToHtmlPrefersToReturnReturnValue(): void
    {
        $this->assertSame(
            '<div>Bar</div>',
            Htmlable::from(function () {
                echo '<div>Foo</div>';

                return '<div>Bar</div>';
            })->toHtml(),
        );
    }

    public function testToHtmlReturnsOutput(): void
    {
        $this->assertSame(
            '<div>Foo</div>',
            Htmlable::from(function (): void {
                echo '<div>Foo</div>';
            })->toHtml(),
        );
    }

    public function testToHtmlRestoresOutputBufferingLevel(): void
    {
        $obLevel = ob_get_level();

        Htmlable::from(function () {
            ob_start();
        })->toHtml();

        $this->assertSame($obLevel, ob_get_level());
    }
}
