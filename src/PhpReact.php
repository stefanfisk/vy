<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact;

use StefanFisk\PhpReact\Serialization\EchoingSerializerInterface;
use StefanFisk\PhpReact\Serialization\Html\HtmlSerializer;
use StefanFisk\PhpReact\Serialization\Html\Middleware\ClassAttributeMiddleware;
use StefanFisk\PhpReact\Serialization\Html\Middleware\ClosureMiddleware;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StringableMiddleware;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StyleAttributeMiddleware;
use StefanFisk\PhpReact\Serialization\SerializerInterface;

use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;

/** @psalm-api */
class PhpReact
{
    public function __construct(
        private readonly Renderer $renderer = new Renderer(),
    ) {
    }

    /**
     * @param SerializerInterface<T> $serializer,
     *
     * @return T
     *
     * @template T
     */
    public function render(
        Element $el,
        SerializerInterface $serializer = new HtmlSerializer(middlewares: [
            new ClosureMiddleware(),
            new StringableMiddleware(),
            new ClassAttributeMiddleware(),
            new StyleAttributeMiddleware(),
        ]),
    ): mixed {
        return $this->renderer->renderAndSerialize(
            el: $el,
            serializer: $serializer,
        );
    }

    public function renderToString(
        Element $el,
        EchoingSerializerInterface $serializer = new HtmlSerializer(middlewares: [
            new ClosureMiddleware(),
            new StringableMiddleware(),
            new ClassAttributeMiddleware(),
            new StyleAttributeMiddleware(),
        ]),
    ): string {
        $obLevel = ob_get_level();

        try {
            ob_start();

            $this->renderer->renderAndSerialize(
                el: $el,
                serializer: $serializer,
            );

            return (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
        }
    }
}
