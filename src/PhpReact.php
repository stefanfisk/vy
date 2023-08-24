<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact;

use Psr\Container\ContainerInterface;
use StefanFisk\PhpReact\Rendering\Comparator;
use StefanFisk\PhpReact\Rendering\NodeFactory;
use StefanFisk\PhpReact\Rendering\Renderer;
use StefanFisk\PhpReact\Serialization\Html\HtmlSerializer;
use StefanFisk\PhpReact\Serialization\Html\Transformers\AttributeValueTransformerInterface;
use StefanFisk\PhpReact\Serialization\Html\Transformers\ChildValueTransformerInterface;
use StefanFisk\PhpReact\Serialization\Html\Transformers\ClassAttributeTransformer;
use StefanFisk\PhpReact\Serialization\Html\Transformers\ClosureTransformer;
use StefanFisk\PhpReact\Serialization\Html\Transformers\StringableTransformer;
use StefanFisk\PhpReact\Serialization\Html\Transformers\StyleAttributeTransformer;

class PhpReact
{
    private readonly HtmlSerializer $serializer;
    private readonly Renderer $renderer;

    /** @param array<AttributeValueTransformerInterface|ChildValueTransformerInterface> $transformers */
    public function __construct(
        ContainerInterface $container = new Container(),
        Comparator $comparator = new Comparator(),
        array $transformers = [
            new ClosureTransformer(),
            new StringableTransformer(),
            new ClassAttributeTransformer(),
            new StyleAttributeTransformer(),
        ],
    ) {
        $this->renderer = new Renderer(
            nodeFactory: new NodeFactory(container: $container),
            comparator: $comparator,
        );
        $this->serializer = new HtmlSerializer(transformers: $transformers);
    }

    public function render(Element $el): string
    {
        $node = $this->renderer->createNode(parent: null, el: $el);

        $this->renderer->enqueueRender($node);

        $this->renderer->processRenderQueue();

        $html = $this->serializer->serialize($node);

        $this->renderer->unmount($node);

        return $html;
    }
}
