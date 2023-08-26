<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html;

interface HtmlableInterface
{
    public function toHtml(): string;
}
