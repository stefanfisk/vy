<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html;

interface HtmlableInterface
{
    public function toHtml(): string;
}
