<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Support;

interface HtmlableInterface
{
    public function toHtml(): string;
}
