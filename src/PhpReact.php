<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact;

use Psr\Container\ContainerInterface;
use StefanFisk\PhpReact\Rendering\NodeFactory;
use StefanFisk\PhpReact\Rendering\Renderer;
use StefanFisk\PhpReact\Serialization\Html\HtmlSerializer;
use StefanFisk\PhpReact\Serialization\Html\Middleware\ClassAttributeMiddleware;
use StefanFisk\PhpReact\Serialization\Html\Middleware\ClosureMiddleware;
use StefanFisk\PhpReact\Serialization\Html\Middleware\HtmlAttributeValueMiddlewareInterface;
use StefanFisk\PhpReact\Serialization\Html\Middleware\HtmlNodeValueMiddlewareInterface;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StringableMiddleware;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StyleAttributeMiddleware;
use StefanFisk\PhpReact\Support\Comparator;

use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;

class PhpReact
{
    private readonly HtmlSerializer $serializer;
    private readonly Renderer $renderer;

    /** @param array<HtmlAttributeValueMiddlewareInterface|HtmlNodeValueMiddlewareInterface> $middlewares */
    public function __construct(
        ContainerInterface $container = new Container(),
        Comparator $comparator = new Comparator(),
        array $middlewares = [
            new ClosureMiddleware(),
            new StringableMiddleware(),
            new ClassAttributeMiddleware(),
            new StyleAttributeMiddleware(),
        ],
    ) {
        $this->renderer = new Renderer(
            nodeFactory: new NodeFactory(container: $container),
            comparator: $comparator,
        );
        $this->serializer = new HtmlSerializer(middlewares: $middlewares);
    }

    public function render(Element $el): void
    {
        $node = $this->renderer->createNode(parent: null, el: $el);

        $this->renderer->giveNodeNextProps(node: $node, nextProps: $el->props);

        $this->renderer->processRenderQueue();

        $this->serializer->serialize($node);

        $this->renderer->unmount($node);
    }

    public function renderToString(Element $el): string
    {
        $obLevel = ob_get_level();

        try {
            ob_start();

            $this->render($el);

            return (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
        }
    }
}
