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

use function assert;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;

/**
 * @psalm-api
 * @template T
 */
class PhpReact
{
    /** @param SerializerInterface<T> $serializer */
    public function __construct(
        private readonly Renderer $renderer = new Renderer(),
        private SerializerInterface $serializer = new HtmlSerializer(middlewares: [
            new ClosureMiddleware(),
            new StringableMiddleware(),
            new ClassAttributeMiddleware(),
            new StyleAttributeMiddleware(),
        ]),
    ) {
    }

    /**
     * @return T
     */
    public function render(
        Element $el,
    ): mixed {
        return $this->renderer->renderAndSerialize(
            el: $el,
            serializer: $this->serializer,
        );
    }

    public function renderToString(
        Element $el,
    ): string {
        assert($this->serializer instanceof EchoingSerializerInterface);

        $obLevel = ob_get_level();

        try {
            ob_start();

            $this->renderer->renderAndSerialize(
                el: $el,
                serializer: $this->serializer, // @phpstan-ignore-line
            );

            return (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
        }
    }
}
