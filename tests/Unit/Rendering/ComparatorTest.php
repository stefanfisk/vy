<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\rendering;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Rendering\Comparator;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Comparator::class)]
class ComparatorTest extends TestCase
{
    private function assertValuesAreEqual(mixed $a, mixed $b): void
    {
        $comparator = new Comparator();

        $this->assertTrue($comparator->valuesAreEqual($a, $b));
    }

    private function assertValuesAreNotEqual(mixed $a, mixed $b): void
    {
        $comparator = new Comparator();

        $this->assertFalse($comparator->valuesAreEqual($a, $b));
    }

    public function testEmptyArraysAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [],
            [],
        );
    }

    public function testIntAndFloatAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [1],
            [1.0],
        );
    }

    public function testIntAndStringAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [1],
            ['1'],
        );
    }

    public function testNestedArraysAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [[1]],
            [[1]],
        );
    }

    public function testArraysWithDifferentKeyOrderAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [
                'foo' => 1,
                'bar' => 2,
                'baz' => 3,
            ],
            [
                'baz' => 3,
                'bar' => 2,
                'foo' => 1,
            ],
        );
    }

    public function testArraysOfDifferentLengthAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            [
                'foo' => 1,
                'bar' => 2,
                'baz' => 3,
            ],
        );
        $this->assertValuesAreNotEqual(
            [
                'foo' => 1,
                'bar' => 2,
                'baz' => 3,
            ],
            [
                'foo' => 1,
                'bar' => 2,
            ],
        );
    }

    public function testIdenticalElementsAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [Element::create('div', ['foo' => 'bar'])('baz')],
            [Element::create('div', ['foo' => 'bar'])('baz')],
        );
    }

    public function testElementsWithTheSameKeysAreEqual(): void
    {
        $this->assertValuesAreEqual(
            [Element::create('div', ['foo' => 'bar'], '1')('baz')],
            [Element::create('div', ['foo' => 'bar'], '1')('baz')],
        );
    }

    public function testElementsWithDifferentKeysAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [Element::create('div', ['foo' => 'bar'], '1')('baz')],
            [Element::create('div', ['foo' => 'bar'], '2')('baz')],
        );
    }

    public function testElementsOfDifferentTypeAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [Element::create('div', ['foo' => 'bar'])('baz')],
            [Element::create('span', ['foo' => 'bar'])('baz')],
        );
    }

    public function testElementsWithDifferentPropsAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            [Element::create('div', ['foo' => 'bar'])('baz')],
            [Element::create('div', ['bar' => 'bar'])('baz')],
        );
    }

    public function testObjectIsEqualToItself(): void
    {
        $object = new stdClass();

        $this->assertValuesAreEqual(
            $object,
            $object,
        );
    }

    public function testObjectsAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            new stdClass(),
            new stdClass(),
        );
    }

    public function testClosureIsEqualToItself(): void
    {
        $closure = fn () => null;

        $this->assertValuesAreEqual(
            $closure,
            $closure,
        );
    }

    public function testIdenticalClosuresAreEqual(): void
    {
        $factory = fn () => fn () => null;

        $this->assertValuesAreEqual(
            $factory(),
            $factory(),
        );
    }

    public function testDifferentClosuresAreNotEqual(): void
    {
        $this->assertValuesAreNotEqual(
            fn () => null,
            fn () => null,
        );
    }

    public function testClosuresWithDifferentStaticAreNotEqual(): void
    {
        $closures = [];
        for ($i = 0; $i < 2; $i++) {
            $closures[] = fn () => $i;
        }

        $this->assertValuesAreNotEqual(
            $closures[0],
            $closures[1],
        );
    }

    public function testClosuresWithDifferentParameterCountsAreNotEqual(): void
    {
        // phpcs:ignore Generic.Formatting.DisallowMultipleStatements.SameLine
        $fn1 = fn ($a, $b) => [$a, $b]; $fn2 = fn ($a) => $a;

        $this->assertValuesAreNotEqual(
            $fn1,
            $fn2,
        );
    }

    public function testClosuresWithDifferentParameterNamesAreNotEqual(): void
    {
        // phpcs:ignore Generic.Formatting.DisallowMultipleStatements.SameLine
        $fn1 = fn (int $i) => $i; $fn2 = fn (string $str) => $str;

        $this->assertValuesAreNotEqual(
            $fn1,
            $fn2,
        );
    }

    public function testClosuresWithDifferentParameterTypesAreNotEqual(): void
    {
        // phpcs:ignore Generic.Formatting.DisallowMultipleStatements.SameLine
        $fn1 = fn (int $x) => $x; $fn2 = fn (string $x) => $x;

        $this->assertValuesAreNotEqual(
            $fn1,
            $fn2,
        );
    }

    public function testClosuresWithDifferentParameterDefaultAvailableAreNotEqual(): void
    {
        // phpcs:ignore Generic.Formatting.DisallowMultipleStatements.SameLine
        $fn1 = fn (int $x) => $x; $fn2 = fn (int $x = 0) => $x;

        $this->assertValuesAreNotEqual(
            $fn1,
            $fn2,
        );
    }

    public function testClosuresWithDifferentParameterDefaultValuesAreNotEqual(): void
    {
        // phpcs:ignore Generic.Formatting.DisallowMultipleStatements.SameLine
        $fn1 = fn (int $x = 0) => $x; $fn2 = fn (int $x = 1) => $x;

        $this->assertValuesAreNotEqual(
            $fn1,
            $fn2,
        );
    }

    public function testClosuresWithDifferentThisAreNotEqual(): void
    {
        $factory = function () {
            $instance = new class {
                public function getFn(): Closure
                {
                    return fn () => $this;
                }
            };

            return $instance->getFn();
        };

        $this->assertValuesAreNotEqual(
            $factory(),
            $factory(),
        );
    }

    public function testClosuresWithDifferentCalledClassAreNotEqual(): void
    {
        $obj1 = new class {
        };
        $obj2 = new class {
        };

        // phpcs:ignore Generic.Formatting.DisallowMultipleStatements.SameLine
        $fn1 = fn () => null; $fn2 = fn () => null;

        $fn1 = Closure::bind($fn1, null, $obj1::class);
        $fn2 = Closure::bind($fn2, null, $obj2::class);

        $this->assertValuesAreNotEqual(
            $fn1,
            $fn2,
        );
    }
}
