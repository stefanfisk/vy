<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Element::class)]
class ElementTest extends TestCase
{
    use MocksComponentsTrait;

    /** @param array{key:(string|int|null),type:mixed,props:array<non-empty-string,mixed>} $expected */
    private function assertElementEquals(array $expected, Element $actual): void
    {
        $this->assertSame(
            $expected,
            [
                'key' => $actual->key,
                'type' => $actual->type,
                'props' => $actual->props,
            ],
        );
    }

    public function testCreateConstructsInstance(): void
    {
        $props = ['foo' => new stdClass()];

        $instance = Element::create(
            type: 'div',
            props: $props,
            key: 'key',
        );

        self::assertSame('div', $instance->type);
        self::assertSame($props, $instance->props);
        self::assertSame('key', $instance->key);
    }

    public function testCreateHasDefaultArguments(): void
    {
        $instance = Element::create(
            type: 'div',
        );

        self::assertSame('div', $instance->type);
        self::assertSame([], $instance->props);
        self::assertNull($instance->key);
    }

    public function testCreateThrowsIfTypeIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create(
            // @phpstan-ignore argument.type
            type: '',
        );
    }

    public function testCreateThrowsIfKeyIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create(
            type: 'div',
            // @phpstan-ignore argument.type
            key: '',
        );
    }

    public function testToChildArrayWrapsSingleItemInArray(): void
    {
        $value = new stdClass();

        $this->assertSame(
            [$value],
            Element::toChildArray($value),
        );
    }

    public function testToChildArrayFlattensArrays(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();
        $value3 = new stdClass();

        $this->assertSame(
            [$value1, $value2, $value3],
            Element::toChildArray([[[$value1, $value2]], $value3]),
        );
    }

    public function testToChildArrayFiltersNullValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, null, $value2]),
        );
    }

    public function testToChildArrayFiltersBoolValues(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, true, false, $value2]),
        );
    }

    public function testToChildArrayFiltersEmptyStrings(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertSame(
            [$value1, $value2],
            Element::toChildArray([$value1, '', $value2]),
        );
    }

    public function testConstructorConstructsInstance(): void
    {
        $props = ['foo' => new stdClass()];

        $instance = new Element(
            type: 'div',
            props: $props,
            key: '123',
        );

        self::assertSame('div', $instance->type);
        self::assertSame($props, $instance->props);
        self::assertSame('123', $instance->key);
    }

    public function testConstructorHasDefaultArguments(): void
    {
        $instance = new Element(
            type: 'div',
        );

        self::assertSame('div', $instance->type);
        self::assertSame([], $instance->props);
        self::assertNull($instance->key);
    }

    public function testConstructorThrowsIfTypeIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Element(
            // @phpstan-ignore argument.type
            type: '',
        );
    }

    public function testConstructorSupportsClosureType(): void
    {
        $fn = fn () => null;

        $instance = new Element(
            type: $fn,
        );

        self::assertSame($fn, $instance->type);
    }

    public function testConstructorThrowsIfKeyIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Element(
            type: 'div',
            // @phpstan-ignore argument.type
            key: '',
        );
    }

    public function testInvokeMergesChildrenIntoProps(): void
    {
        $this->assertElementEquals(
            [
                'key' => null,
                'type' => 'div',
                'props' => [
                    'foo' => 'bar',
                    'children' => [['baz', 'qux'], 'quux'],
                ],
            ],
            Element::create('div', [
                'foo' => 'bar',
            ])(
                [
                    'baz',
                    'qux',
                ],
                'quux',
            ),
        );
    }

    public function testInvokeThrowsIfBothChildrenPropAndChildren(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Element::create('div', [
            'foo' => 'bar',
            'children' => 'baz',
        ])(
            'quz',
        );
    }
}
