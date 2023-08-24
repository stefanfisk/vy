<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Transformers;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Serialization\Html\Transformers\ValueTransformer;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\PhpReact\Tests\TestCase;

#[CoversClass(ValueTransformer::class)]
class ValueTransformerTest extends TestCase
{
    use MocksInvokablesTrait;

    private ValueTransformer&MockInterface $transformer;

    protected function setUp(): void
    {
        $this->transformer = $this->mockery(ValueTransformer::class)
            ->makePartial();
    }

    public function testCallsTransformValueForAttributes(): void
    {
        $this->transformer
            ->shouldReceive('transformValue')
            ->once()
            ->with('bar')
            ->andReturn('baz');

        $ret = $this->transformer->processAttributeValue(
            name: 'foo',
            value: 'bar',
        );

        $this->assertSame('baz', $ret);
    }

    public function testCallsTransformValueForNode(): void
    {
        $this->transformer
            ->shouldReceive('transformValue')
            ->once()
            ->with('foo')
            ->andReturn('bar');

        $ret = $this->transformer->processChildValue(
            value: 'foo',
        );

        $this->assertSame('bar', $ret);
    }
}
