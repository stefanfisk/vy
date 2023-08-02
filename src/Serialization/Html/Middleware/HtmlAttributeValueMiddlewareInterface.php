<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Middleware;

use Closure;

interface HtmlAttributeValueMiddlewareInterface
{
    /** @param Closure(mixed $value):mixed $next */
    public function processAttributeValue(string $name, mixed $value, Closure $next): mixed;
}
