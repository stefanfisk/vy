<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StyleAttributeMiddleware;
use StefanFisk\PhpReact\Tests\Support\Mocks\MockInvokable;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use Throwable;
use stdClass;

/**
 * @covers StefanFisk\PhpReact\Serialization\Html\Middleware\StyleAttributeMiddleware
 */
class StyleAttributeMiddlewareTest extends TestCase
{
    use MocksInvokablesTrait;

    private StyleAttributeMiddleware $middleware;
    private MockInvokable $next;

    protected function setUp(): void
    {
        $this->middleware = new StyleAttributeMiddleware();
        $this->next = $this->createInvokableMock();
    }

    private function assertStyleEquals(string $expected, mixed $value): void
    {
        $this->next
            ->expects($this->once())
            ->with($expected)
            ->willReturn($expected);

        $this->assertSame(
            $expected,
            $this->middleware->processAttributeValue(
                name: 'style',
                value: $value,
                next: ($this->next)(...),
            ),
        );
    }

    /** @param class-string<Throwable> $exception */
    private function assertThrowsForStyle(string $exception, mixed $value): void
    {
        $this->expectException($exception);

        $this->middleware->processAttributeValue(
            name: 'style',
            value: $value,
            next: ($this->next)(...),
        );
    }

    public function testIgnoresNonStyleAttributes(): void
    {
        $value = new stdClass();

        $this->next
            ->expects($this->once())
            ->with($value)
            ->willReturn($value);

        $this->assertSame(
            $value,
            $this->middleware->processAttributeValue(
                name: 'foo',
                value: $value,
                next: ($this->next)(...),
            ),
        );
    }

    public function testThrowsForObjectValue(): void
    {
        $this->assertThrowsForStyle(InvalidArgumentException::class, new stdClass());
    }

    public function testThrowsForObjectSubValue(): void
    {
        $this->assertThrowsForStyle(InvalidArgumentException::class, [new stdClass()]);
    }

    public function testNull(): void
    {
        $this->assertStyleEquals('', null);
    }

    public function testEmptyString(): void
    {
        $this->assertStyleEquals('', '');
    }

    public function testString(): void
    {
        $this->assertStyleEquals('foo:bar', 'foo:bar');
    }

    public function testIndexedString(): void
    {
        $this->assertStyleEquals('foo:bar', ['foo:bar']);
    }

    public function testKeyedString(): void
    {
        $this->assertStyleEquals('foo:bar', ['foo' => 'bar']);
    }

    public function testNestedStrings(): void
    {
        $this->assertStyleEquals('foo:bar;baz:qux', [
            ['foo' => 'bar'],
            ['baz' => 'qux'],
        ]);
    }
}
