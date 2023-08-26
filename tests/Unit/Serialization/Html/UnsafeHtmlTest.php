<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Serialization\Html\UnsafeHtml;
use StefanFisk\Vy\Tests\TestCase;
use Stringable;
use UnexpectedValueException;
use stdClass;

use function ob_get_level;
use function ob_start;

#[CoversClass(UnsafeHtml::class)]
class UnsafeHtmlTest extends TestCase
{
    public function testFromReturnsInstance(): void
    {
        $this->assertEquals(
            new UnsafeHtml('<div>Foo</div>'),
            UnsafeHtml::from('<div>Foo</div>'),
        );
    }

    public function testToHtmlReturnsString(): void
    {
        $this->assertSame(
            '<div>Foo</div>',
            UnsafeHtml::from('<div>Foo</div>')->toHtml(),
        );
    }

    public function testToHtmlReturnsIntReturnValue(): void
    {
        $this->assertSame(
            '123',
            UnsafeHtml::from(fn () => 123)->toHtml(),
        );
    }

    public function testToHtmlReturnsFloatReturnValue(): void
    {
        $this->assertSame(
            '123.456',
            UnsafeHtml::from(fn () => 123.456)->toHtml(),
        );
    }

    public function testToHtmlReturnsStringReturnValue(): void
    {
        $this->assertSame(
            '<div>Foo</div>',
            UnsafeHtml::from(fn () => '<div>Foo</div>')->toHtml(),
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
            UnsafeHtml::from(fn () => $value)->toHtml(),
        );
    }

    public function testToHtmlThrowsForBoolReturnValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        UnsafeHtml::from(fn () => true)->toHtml();
    }

    public function testToHtmlThrowsForNonStringableObjectReturnValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        UnsafeHtml::from(fn () => new stdClass())->toHtml();
    }

    public function testToHtmlPrefersToReturnReturnValue(): void
    {
        $this->assertSame(
            '<div>Bar</div>',
            UnsafeHtml::from(function () {
                echo '<div>Foo</div>';

                return '<div>Bar</div>';
            })->toHtml(),
        );
    }

    public function testToHtmlReturnsOutput(): void
    {
        $this->assertSame(
            '<div>Foo</div>',
            UnsafeHtml::from(function (): void {
                echo '<div>Foo</div>';
            })->toHtml(),
        );
    }

    public function testToHtmlRestoresOutputBufferingLevel(): void
    {
        $obLevel = ob_get_level();

        UnsafeHtml::from(function () {
            ob_start();
        })->toHtml();

        $this->assertSame($obLevel, ob_get_level());
    }
}
