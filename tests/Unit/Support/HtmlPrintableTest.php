<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Support\HtmlPrintable;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;

use function ob_get_clean;
use function ob_start;

#[CoversClass(HtmlPrintable::class)]
class HtmlPrintableTest extends TestCase
{
    use MocksInvokablesTrait;

    public function testFromReturnsInstance(): void
    {
        $fn = $this->createInvokableMock();
        $fn
            ->expects($this->once())
            ->willReturnCallback(function () {
                echo 'Foo';
            });

        $printable = HtmlPrintable::from($fn(...));

        ob_start();
        $printable->printHtml();
        $output = ob_get_clean();

        $this->assertSame('Foo', $output);
    }

    public function testPrintOutputsFnOutput(): void
    {
        $fn = $this->createInvokableMock();
        $fn
            ->expects($this->once())
            ->willReturnCallback(function () {
                echo 'Foo';
            });

        $printable = new HtmlPrintable($fn(...));

        ob_start();
        $printable->printHtml();
        $output = ob_get_clean();

        $this->assertSame('Foo', $output);
    }
}
