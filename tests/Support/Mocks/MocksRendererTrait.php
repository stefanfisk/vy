<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Before;
use StefanFisk\PhpReact\Rendering\Renderer;

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
