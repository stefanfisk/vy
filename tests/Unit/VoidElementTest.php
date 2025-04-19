<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use StefanFisk\Vy\VoidElement;
use Throwable;

use function array_key_exists;

#[CoversClass(VoidElement::class)]
class VoidElementTest extends TestCase
{
    use MocksComponentsTrait;

    /**
     * @return array<string,array{0:array{0?:string|Closure,1?:array<mixed>,2?:?string},1?:class-string<Throwable>}>
     */
    public static function provideTestCases(): array
    {
        return [
            'null children does not throw' => [
                ['void', ['foo' => 'bar', 'children' => null]],
            ],
            'empty array children does not throw' => [
                ['void', ['foo' => 'bar', 'children' => []]],
            ],
            'true children does not throw' => [
                ['void', ['foo' => 'bar', 'children' => true]],
            ],
            'false children does not throw' => [
                ['void', ['foo' => 'bar', 'children' => false]],
            ],
            'empty children does not throw' => [
                ['void', ['foo' => 'bar', 'children' => '']],
            ],
            'mixed null bool empty string children does not throw' => [
                ['void', ['foo' => 'bar', 'children' => [true, '']]],
            ],
            'no arguments throws' => [
                [], ArgumentCountError::class,
            ],
            'non empty string children throws' => [
                ['void', ['foo' => 'bar', 'children' => ' ']], InvalidArgumentException::class,
            ],
            'element children throws' => [
                ['void', ['foo' => 'bar', 'children' => new Element('foo')]], InvalidArgumentException::class,
            ],
        ];
    }

    /**
     * @param array{0?:string|Closure,1?:array<mixed>,2?:?string} $args
     * @param ?class-string<Throwable> $exception
     * @param Closure():VoidElement $fn
     */
    private function assertCase(array $args, ?string $exception, Closure $fn): void
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $el = $fn();

        if (array_key_exists(0, $args)) {
            $this->assertSame($args[0], $el->type);
        }

        if (array_key_exists(1, $args)) {
            $this->assertSame($args[1], $el->props);
        } else {
            $this->assertSame([], $el->props);
        }

        if (array_key_exists(2, $args)) {
            $this->assertSame($args[2], $el->key);
        } else {
            $this->assertNull($el->key);
        }
    }

    /**
     * @param array{0?:string|Closure,1?:array<mixed>,2?:?string} $args
     * @param ?class-string<Throwable> $exception
     */
    #[DataProvider('provideTestCases')]
    public function testCreate(array $args, ?string $exception = null): void
    {
        // @phpstan-ignore arguments.count
        $this->assertCase($args, $exception, fn () => VoidElement::create(...$args));
    }

    /**
     * @param array{0?:string|Closure,1?:array<mixed>,2?:?string} $args
     * @param ?class-string<Throwable> $exception
     */
    #[DataProvider('provideTestCases')]
    public function testConstructor(array $args, ?string $exception = null): void
    {
        // @phpstan-ignore arguments.count
        $this->assertCase($args, $exception, fn () => new VoidElement(...$args));
    }

    public function testInstancesAreNotInvokable(): void
    {
        $el = VoidElement::create('div');

        $this->expectException(Throwable::class);

        // @phpstan-ignore callable.nonCallable
        $el('test');
    }
}
