<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Support\Mocks;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use StefanFisk\PhpReact\Rendering\Renderer;

trait MocksRendererTrait
{
    protected Renderer&MockObject $renderer;

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $className
     * @psalm-return MockBuilder<RealInstanceType>
     */
    abstract public function getMockBuilder(string $className): MockBuilder;

    #[Before]
    protected function setMocksRendererTrait(): void
    {
        $this->renderer = $this->getMockBuilder(Renderer::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();
    }
}
