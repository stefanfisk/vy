<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\RawTextElement;
use StefanFisk\Vy\Serialization\Html\UnsafeHtml;
use StefanFisk\Vy\Tests\Support\Mocks\MocksComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use Throwable;

use function array_key_exists;

#[CoversClass(RawTextElement::class)]
class RawTextElementTest extends TestCase
{
    use MocksComponentsTrait;

    /**
     * @return array<string,array{0:array{0?:string|Closure,1?:array<mixed>,2?:?string},1?:class-string<Throwable>}>
     */
    public static function provideConstructorCases(): array
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
            'single Htmlable child does not throws' => [
                ['void', ['foo' => 'bar', 'children' => UnsafeHtml::from('Test')]],
            ],
            'multiple Htmlable childred throws' => [
                [
                    'void',
                    [
                        'children' => [
                            UnsafeHtml::from('Test 1'),
                            UnsafeHtml::from('Test 2'),
                        ],
                    ],
                ],
                InvalidArgumentException::class,
            ],
        ];
    }

    /**
     * @param array{0?:string|Closure,1?:array<mixed>,2?:?string} $args
     * @param ?class-string<Throwable> $exception
     * @param Closure():RawTextElement $fn
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
    #[DataProvider('provideConstructorCases')]
    public function testCreate(array $args, ?string $exception = null): void
    {
        // @phpstan-ignore arguments.count
        $this->assertCase($args, $exception, fn () => RawTextElement::create(...$args));
    }

    /**
     * @param array{0?:string|Closure,1?:array<mixed>,2?:?string} $args
     * @param ?class-string<Throwable> $exception
     */
    #[DataProvider('provideConstructorCases')]
    public function testConstructor(array $args, ?string $exception = null): void
    {
        // @phpstan-ignore arguments.count
        $this->assertCase($args, $exception, fn () => new RawTextElement(...$args));
    }

    /**
     * @return array<array{0:array<mixed>,1:array<mixed>,2?:class-string<Throwable>}>
     */
    public static function provideInvokeCases(): array
    {
        return [
            'no argument throws' => [[], [], ArgumentCountError::class],
        ];
    }

    /**
     * @param array<mixed> $props
     * @param array<mixed> $args
     * @param ?class-string<Throwable> $exception
     */
    #[DataProvider('provideInvokeCases')]
    public function testInvoke(array $props, array $args, ?string $exception): void
    {
        $el = RawTextElement::create('div', $props);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        // @phpstan-ignore argument.type
        $el(...$args);
    }
}
