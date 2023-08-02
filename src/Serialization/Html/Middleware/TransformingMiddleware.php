<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Middleware;

use Closure;

abstract class TransformingMiddleware implements HtmlAttributeValueMiddlewareInterface, HtmlNodeValueMiddlewareInterface
{
    /** {@inheritDoc} */
    final public function processAttributeValue(string $name, mixed $value, Closure $next): mixed
    {
        return $next($this->transformValue($value));
    }

    /** {@inheritDoc} */
    final public function processNodeValue(mixed $value, Closure $next): mixed
    {
        return $next($this->transformValue($value));
    }

    abstract public function transformValue(mixed $value): mixed;
}
