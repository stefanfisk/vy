<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Serialization\Html\CachingPropToAttrNameMapper;
use StefanFisk\Vy\Serialization\Html\PropToAttrNameMapper;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(CachingPropToAttrNameMapper::class)]
class CachingPropToAttrNameMapperTest extends TestCase
{
    private CachingPropToAttrNameMapper $mapper;

    public function testUsesStaticMap(): void
    {
        $this->mapper = new CachingPropToAttrNameMapper(
            propToAttrName: [
                'foo' => 'bar',
            ],
            mappers: [],
        );

        $this->assertSame('bar', $this->mapper->propToAttrName('foo'));
    }

    public function testCallsMappersInOrder(): void
    {
        $m1 = $this->mockery(PropToAttrNameMapper::class);
        $m2 = $this->mockery(PropToAttrNameMapper::class);
        $m3 = $this->mockery(PropToAttrNameMapper::class);

        $m1->shouldReceive('propToAttrName')->with('foo')->once()->andReturn(null)->globally()->ordered();
        $m2->shouldReceive('propToAttrName')->with('foo')->once()->andReturn(null)->globally()->ordered();
        $m3->shouldReceive('propToAttrName')->with('foo')->once()->andReturn('bar')->globally()->ordered();

        $this->mapper = new CachingPropToAttrNameMapper(mappers: [$m1, $m2, $m3], propToAttrName: []);

        $this->assertSame('bar', $this->mapper->propToAttrName('foo'));
    }

    public function testStopsCallingMappersAfterFirstMatch(): void
    {
        $m1 = $this->mockery(PropToAttrNameMapper::class);
        $m2 = $this->mockery(PropToAttrNameMapper::class);
        $m3 = $this->mockery(PropToAttrNameMapper::class);

        $m1->shouldReceive('propToAttrName')->with('foo')->once()->andReturn(null);
        $m2->shouldReceive('propToAttrName')->with('foo')->once()->andReturn('bar');

        $this->mapper = new CachingPropToAttrNameMapper(mappers: [$m1, $m2, $m3], propToAttrName: []);

        $this->assertSame('bar', $this->mapper->propToAttrName('foo'));
    }

    public function testCachesFirstResult(): void
    {
        $m1 = $this->mockery(PropToAttrNameMapper::class);
        $m2 = $this->mockery(PropToAttrNameMapper::class);
        $m3 = $this->mockery(PropToAttrNameMapper::class);

        $m1->shouldReceive('propToAttrName')->with('foo')->once()->andReturn(null);
        $m2->shouldReceive('propToAttrName')->with('foo')->once()->andReturn('bar');

        $this->mapper = new CachingPropToAttrNameMapper(mappers: [$m1, $m2, $m3], propToAttrName: []);

        $this->mapper->propToAttrName('foo');

        $this->assertSame('bar', $this->mapper->propToAttrName('foo'));
    }
}
