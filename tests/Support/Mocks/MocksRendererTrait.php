<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Support\Mocks;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Before;
use StefanFisk\Vy\Rendering\Renderer;

trait MocksRendererTrait
{
    use MockeryTrait;

    protected Renderer&MockInterface $renderer;

    #[Before]
    protected function setUpMocksRendererTrait(): void
    {
        $this->renderer = $this->mockery(Renderer::class);
    }
}
