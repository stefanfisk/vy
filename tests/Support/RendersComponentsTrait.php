<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support;

use Closure;
use StefanFisk\Vy\BaseElement;

trait RendersComponentsTrait
{
    protected function renderComponent(BaseElement $el): mixed
    {
        $this->assertInstanceOf(Closure::class, $el->type);

        $render = $el->type;

        return $render(...$el->props);
    }
}
