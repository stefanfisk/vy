<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use InvalidArgumentException;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Serialization\Html\Middleware\StyleAttributeMiddleware;
use StefanFisk\PhpReact\Tests\Support\Mocks\Invokable;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\PhpReact\Tests\TestCase;
use Throwable;
use stdClass;

#[CoversClass(StyleAttributeMiddleware::class)]
class StyleAttributeMiddlewareTest extends TestCase
{
    use MocksInvokablesTrait;

    private StyleAttributeMiddleware $middleware;
    private Invokable&MockInterface $next;

    protected function setUp(): void
    {
        $this->middleware = new StyleAttributeMiddleware();
        $this->next = $this->createMockInvokable();
    }

    private function assertStyleEquals(string $expected, mixed $value): void
    {
        $this->next
            ->shouldReceive('__invoke')
            ->once()
            ->with($expected)
            ->andReturn($expected);

        $this->assertSame(
            $expected,
            $this->middleware->processAttributeValue(
                name: 'style',
                value: $value,
                next: $this->next->fn,
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
            next: $this->next->fn,
        );
    }

    public function testIgnoresNonStyleAttributes(): void
    {
        $value = new stdClass();

        $this->next
            ->shouldReceive('__invoke')
            ->once()
            ->with($value)
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->middleware->processAttributeValue(
                name: 'foo',
                value: $value,
                next: $this->next->fn,
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
