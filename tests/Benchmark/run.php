<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Benchmark;

use DOMAttr;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Masterminds\HTML5;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Parsing\HtmlParser;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Renderer;
use StefanFisk\Vy\Serialization\Html\HtmlSerializer;
use StefanFisk\Vy\Serialization\Html\Transformers\ClassAttributeTransformer;
use StefanFisk\Vy\Serialization\Html\Transformers\ClosureTransformer;
use StefanFisk\Vy\Serialization\Html\Transformers\StringableTransformer;
use StefanFisk\Vy\Serialization\Html\Transformers\StyleAttributeTransformer;
use StefanFisk\Vy\Tests\Support\PassthroughPropToAttrNameMapper;
use StefanFisk\Vy\Vy;

use function array_sum;
use function assert;
use function count;
use function explode;
use function file_get_contents;
use function function_exists;
use function in_array;
use function is_string;
use function microtime;

require __DIR__ . '/../../vendor/autoload.php';

$testIds = explode(',', $argv[1] ?? 'parse,render,serialize-no-transformer,serialize');
$iterations = $argv[2] ?? 100;

echo "$iterations iterations...\n";

$exampleHtml = file_get_contents(__DIR__ . '/../Fixtures/example.html');
assert(is_string($exampleHtml));

$tests = [
    'html5-parse' => [
        fn () => [new HTML5(), $exampleHtml],
        function (HTML5 $html5, string $exampleHtml) {
            $html5->loadHTML($exampleHtml);
        },
    ],
    'parse' => [
        fn () => [new HtmlParser(), $exampleHtml],
        function (HtmlParser $parser, string $exampleHtml) {
            $parser->parseDocument($exampleHtml);
        },
    ],
    'render' => [
        function () use ($exampleHtml) {
            $renderer = new Renderer();

            $parser = new HtmlParser();
            $el = $parser->parseDocument($exampleHtml);
            assert($el instanceof Element);

            return [$renderer, $el];
        },
        function (Renderer $renderer, Element $el) {
            $node = $renderer->createNode(parent: null, el: $el);

            $renderer->enqueueRender($node);

            $renderer->processRenderQueue();
        },
    ],
    'html5-serialize' => [
        function () use ($exampleHtml) {
            $html5 = new HTML5();
            $doc = $html5->loadHTML($exampleHtml);

            return [$html5, $doc];
        },
        function (HTML5 $html5, DOMDocument $doc) {
            $html5->saveHTML($doc);
        },
    ],
    'serialize-no-transformer' => [
        function () use ($exampleHtml) {
            $parser = new HtmlParser();
            $el = $parser->parseDocument($exampleHtml);
            assert($el instanceof Element);

            $renderer = new Renderer();

            $node = $renderer->createNode(parent: null, el: $el);

            $renderer->enqueueRender($node);

            $renderer->processRenderQueue();

            $serializer = new HtmlSerializer(
                propToAttrNameMapper: new PassthroughPropToAttrNameMapper(),
                transformers: [],
            );

            return [$serializer, $node];
        },
        function (HtmlSerializer $serializer, Node $node) {
            $serializer->serialize($node);
        },
    ],
    'serialize' => [
        function () use ($exampleHtml) {
            $parser = new HtmlParser();
            $el = $parser->parseDocument($exampleHtml);
            assert($el instanceof Element);

            $renderer = new Renderer();

            $node = $renderer->createNode(parent: null, el: $el);

            $renderer->enqueueRender($node);

            $renderer->processRenderQueue();

            $serializer = new HtmlSerializer(
                propToAttrNameMapper: new PassthroughPropToAttrNameMapper(),
                transformers: [
                    new ClosureTransformer(),
                    new StringableTransformer(),
                    new ClassAttributeTransformer(),
                    new StyleAttributeTransformer(),
                ],
            );

            return [$serializer, $node];
        },
        function (HtmlSerializer $serializer, Node $node) {
            $serializer->serialize($node);
        },
    ],
    'render-and-serialize' => [
        function () use ($exampleHtml) {
            $parser = new HtmlParser();
            $el = $parser->parseDocument($exampleHtml);
            assert($el instanceof Element);

            $vy = new Vy();

            return [$vy, $el];
        },
        function (Vy $vy, Element $el) {
            $vy->render($el);
        },
    ],
    'class-attribute-transformer' => [
        function () use ($exampleHtml) {
            $html5 = new HTML5();
            $doc = $html5->loadHTML($exampleHtml);

            $xpath = new DOMXPath($doc);

            /** @var DOMNodeList<DOMAttr> $attrNodes */
            $attrNodes = $xpath->query('//@class');

            $classes = [];

            foreach ($attrNodes as $attrNode) {
                $classes[] = $attrNode->value;
            }

            return [new ClassAttributeTransformer(), $classes];
        },
        function (ClassAttributeTransformer $transformer, array $classes) {
            foreach ($classes as $class) {
                $transformer->processAttributeValue('class', $class);
            }
        },
    ],
];

foreach ($tests as $testId => [$setup, $execute]) {
    if (!in_array($testId, $testIds)) {
        continue;
    }

    $args = $setup();

    $samples = [];

    echo "$testId: ";

    if (function_exists('spx_profiler_start')) {
        spx_profiler_start();
        // @phpstan-ignore-next-line
        spx_profiler_full_report_set_custom_metadata_str("Benchmark: $testId, $iterations iterations.");
    }

    for ($i = 0; $i < $iterations; ++$i) {
        $t = microtime(true);

        $execute(...$args);

        $samples[] = microtime(true) - $t;
    }

    if (function_exists('spx_profiler_stop')) {
        spx_profiler_stop();
    }

    $time = array_sum($samples) / count($samples) * 1000;

    echo "$time ms\n";
}

exit(0);
