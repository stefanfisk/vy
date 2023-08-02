<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Support\HtmlString;

#[CoversClass(HtmlString::class)]
class HtmlStringTest extends TestCase
{
    public function testFromReturnsInstance(): void
    {
        $this->assertEquals(
            new HtmlString('<div>Foo</div>'),
            HtmlString::from('<div>Foo</div>'),
        );
    }

    public function testToHtmlReturnsString(): void
    {
        $this->assertSame(
            '<div>Foo</div>',
            HtmlString::from('<div>Foo</div>')->toHtml(),
        );
    }
}
