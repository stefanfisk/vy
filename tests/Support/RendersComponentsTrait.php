<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support;

use Closure;
use StefanFisk\Vy\Element;

trait RendersComponentsTrait
{
    protected function renderComponent(Element $el): mixed
    {
        $this->assertInstanceOf(Closure::class, $el->type);

        $render = $el->type;

        return $render(...$el->props);
    }
}
