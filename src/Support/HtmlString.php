<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Support;

final class HtmlString implements HtmlableInterface
{
    public static function from(string $html): static
    {
        return new static($html);
    }

    public function __construct(private readonly string $html)
    {
    }

    public function toHtml(): string
    {
        return $this->html;
    }
}
