<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Integration;

use Masterminds\HTML5;
use PHPUnit\Framework\Attributes\CoversNothing;
use StefanFisk\PhpReact\Element;
use StefanFisk\PhpReact\Parsing\HtmlParser;
use StefanFisk\PhpReact\PhpReact;
use StefanFisk\PhpReact\Tests\TestCase;

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

        $phpReact = new PhpReact(transformers: []);
        $actual = $phpReact->render($el);

        $this->assertSame($expected, $actual);
    }
}
