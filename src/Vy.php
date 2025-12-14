<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use StefanFisk\Vy\Rendering\Comparator;
use StefanFisk\Vy\Rendering\Renderer;
use StefanFisk\Vy\Serialization\Html\HtmlSerializer;
use StefanFisk\Vy\Serialization\Html\Transformers\AttributesTransformerInterface;
use StefanFisk\Vy\Serialization\Html\Transformers\ChildValueTransformerInterface;
use StefanFisk\Vy\Serialization\Html\Transformers\ClassAttributeTransformer;
use StefanFisk\Vy\Serialization\Html\Transformers\ClosureTransformer;
use StefanFisk\Vy\Serialization\Html\Transformers\StringableTransformer;
use StefanFisk\Vy\Serialization\Html\Transformers\StyleAttributeTransformer;

final class Vy
{
    private readonly HtmlSerializer $serializer;
    private readonly Renderer $renderer;

    /**
     * @param array<AttributesTransformerInterface|ChildValueTransformerInterface> $transformers
     */
    public function __construct(
        Comparator $comparator = new Comparator(),
        array $transformers = [
            new ClosureTransformer(),
            new StringableTransformer(),
            new ClassAttributeTransformer(),
            new StyleAttributeTransformer(),
        ],
        private readonly ?Element $rootComponent = null,
        bool $encodeEntities = false,
        bool $debugComponents = false,
    ) {
        $this->renderer = new Renderer(
            comparator: $comparator,
        );
        $this->serializer = new HtmlSerializer(
            transformers: $transformers,
            encodeEntities: $encodeEntities,
            debugComponents: $debugComponents,
        );
    }

    public function render(Element $el): string
    {
        if ($this->rootComponent) {
            $el = ($this->rootComponent)(
                $el,
            );
        }

        $node = $this->renderer->createNode(parent: null, el: $el);

        $this->renderer->enqueueRender($node);

        $this->renderer->processRenderQueue();

        return $this->serializer->serialize($node);
    }
}
