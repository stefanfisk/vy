<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Serialization\Html\DefaultPropToAttrNameMapper;
use StefanFisk\Vy\Serialization\Html\PropToAttrNameMapper;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(DefaultPropToAttrNameMapper::class)]
class DefaultPropToAttrNameMapperTest extends TestCase
{
    private PropToAttrNameMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new DefaultPropToAttrNameMapper();
    }

    /**
     * @param non-empty-string $propName
     * @param non-empty-string $expectedAttributeName
     */
    private function assertMapsTo(string $propName, string $expectedAttributeName): void
    {
        $this->assertSame($expectedAttributeName, $this->mapper->propToAttrName($propName));
    }

    public function testDoesNotModifyLowerKebabCase(): void
    {
        $this->assertMapsTo('foo-bar', 'foo-bar');
    }

    public function testMapsCamelCaseToKebabCase(): void
    {
        $this->assertMapsTo('fooBar', 'foo-bar');
    }

    public function testDoesNotTreatNumberAsWordDelimiter(): void
    {
        $this->assertMapsTo('foo1Bar', 'foo1-bar');
    }
}
