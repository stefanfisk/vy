<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit\Serialization\Html\Middleware;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Serialization\Html\Middleware\ClassAttributeMiddleware;
use StefanFisk\PhpReact\Tests\Support\Mocks\MockInvokable;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksInvokablesTrait;
use StefanFisk\PhpReact\Tests\TestCase;
use stdClass;

#[CoversClass(ClassAttributeMiddleware::class)]
class ClassAttributeMiddlewareTest extends TestCase
{
    use MocksInvokablesTrait;

    private ClassAttributeMiddleware $middleware;
    private MockInvokable $next;

    protected function setUp(): void
    {
        $this->middleware = new ClassAttributeMiddleware();
        $this->next = $this->createInvokableMock();
    }

    private function assertClassEquals(string $expected, mixed $value): void
    {
        $this->next
            ->expects($this->once())
            ->with($expected)
            ->willReturn($expected);

        $this->assertSame(
            $expected,
            $this->middleware->processAttributeValue(
                name: 'class',
                value: $value,
                next: ($this->next)(...),
            ),
        );
    }

    public function testIgnoresNonClassAttributes(): void
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
        $this->expectException(InvalidArgumentException::class);

        $this->middleware->processAttributeValue(
            name: 'class',
            value: new stdClass(),
            next: ($this->next)(...),
        );
    }

    public function testThrowsForObjectSubvalue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->middleware->processAttributeValue(
            name: 'class',
            value: [new stdClass()],
            next: ($this->next)(...),
        );
    }

    public function testNull(): void
    {
        $this->assertClassEquals('', null);
    }

    public function testEmptyString(): void
    {
        $this->assertClassEquals('', '');
    }

    public function testSortsString(): void
    {
        $this->assertClassEquals('bar foo', 'foo bar');
    }

    public function testConditional(): void
    {
        $this->assertClassEquals(
            'bar foo',
            [
                'foo',
                'bar' => true,
                'baz' => false,
            ],
        );
    }

    public function testNestedConditional(): void
    {
        $this->assertClassEquals(
            'bar foo',
            [
                'foo',
                ['bar' => true],
                ['baz' => false],
            ],
        );
    }

    public function testSortsConditional(): void
    {
        $this->assertClassEquals(
            'bar baz foo',
            [
                'foo bar' => true,
                'baz',
            ],
        );
    }

    public function testConflictingConditionals(): void
    {
        $this->assertClassEquals(
            'foo',
            [
                ['foo' => true],
                ['foo' => false],
            ],
        );
    }
}
