<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Integration;

use Masterminds\HTML5;
use PHPUnit\Framework\Attributes\CoversNothing;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Parsing\HtmlParser;
use StefanFisk\Vy\Tests\TestCase;
use StefanFisk\Vy\Vy;

use function file_get_contents;

#[CoversNothing]
class HtmlParserTest extends TestCase
{
    public function testExampleHtml(): void
    {
        $exampleHtml = file_get_contents(__DIR__ . '/../../Fixtures/example.html') ?: '';

        $html5 = new HTML5();
        $parser = new HtmlParser($html5);

        $expected = $html5->saveHTML($html5->parse($exampleHtml));

        /** @var Element $el */
        $el = $parser->parseDocument($exampleHtml);

        $vy = new Vy(transformers: []);
        $actual = $vy->render($el);

        $this->assertSame($expected, $actual);
    }
}
