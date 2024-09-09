<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use StefanFisk\Vy\VoidElement;

#[CoversClass(VoidElement::class)]
class VoidElementTest extends TestCase
{
    use MocksComponentsTrait;

    /**
     * @param ?non-empty-string $key
     * @param array<string,mixed> $props
     */
    private function assertDoesNotThrow(string | Closure $type, ?string $key, array $props): void
    {
        $el = new VoidElement($type, $key, $props);

        $this->assertSame($type, $el->type);
        $this->assertSame($key, $el->key);
        $this->assertSame($props, $el->props);
    }

    /**
     * @param ?non-empty-string $key
     * @param array<string,mixed> $props
     */
    private function assertThrows(string | Closure $type, ?string $key, array $props): void
    {
        $this->expectException(InvalidArgumentException::class);

        new VoidElement($type, $key, $props);
    }

    public function testDoesNotThrowWhereThereIsNoChildrenKey(): void
    {
        $this->assertDoesNotThrow('void', 'my_key', ['foo' => 'bar']);
    }

    public function testDoesNotThrowForNullChildren(): void
    {
        $this->assertDoesNotThrow('void', null, ['foo' => 'bar', 'children' => null]);
    }

    public function testDoesNotThrowForEmptyArrayChildren(): void
    {
        $this->assertDoesNotThrow('void', null, ['foo' => 'bar', 'children' => []]);
    }

    public function testDoesNotThrowForTrueChildren(): void
    {
        $this->assertDoesNotThrow('void', null, ['foo' => 'bar', 'children' => true]);
    }

    public function testDoesNotThrowForFalseChildren(): void
    {
        $this->assertDoesNotThrow('void', null, ['foo' => 'bar', 'children' => false]);
    }

    public function testDoesNotThrowForEmptyChildren(): void
    {
        $this->assertDoesNotThrow('void', null, ['foo' => 'bar', 'children' => '']);
    }

    public function testDoesNotThrowForMixedNullBoolEmptyStringChildren(): void
    {
        $this->assertDoesNotThrow('void', null, ['foo' => 'bar', 'children' => [null, true, false, '']]);
    }

    public function testThrowsForNonEmptyStringChildren(): void
    {
        $this->assertThrows('void', null, ['foo' => 'bar', 'children' => ' ']);
    }

    public function testThrowsForElementChildren(): void
    {
        $this->assertThrows('void', null, ['foo' => 'bar', 'children' => new Element('foo')]);
    }
}
