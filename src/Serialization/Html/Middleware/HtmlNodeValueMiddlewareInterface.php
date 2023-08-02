<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Middleware;

use Closure;

interface HtmlNodeValueMiddlewareInterface
{
    /** @param Closure(mixed $value):mixed $next */
    public function processNodeValue(mixed $value, Closure $next): mixed;
}
