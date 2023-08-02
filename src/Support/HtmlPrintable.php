<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Support;

use Closure;

final class HtmlPrintable implements HtmlPrintableInterface
{
    public static function from(Closure $fn): static
    {
        return new static($fn);
    }

    public function __construct(private readonly Closure $fn)
    {
    }

    public function printHtml(): void
    {
        ($this->fn)();
    }
}
