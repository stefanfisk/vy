<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit;

use ArgumentCountError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Container;
use StefanFisk\PhpReact\Errors\ContainerException;
use StefanFisk\PhpReact\Errors\EntryNotFoundException;

#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testHasReturnsFalseForNonExistingClass(): void
    {
        $this->assertFalse($this->container->has(static::class . '123'));
    }

    public function testHasReturnsTrueForExistingClass(): void
    {
        $this->assertTrue($this->container->has(static::class));
    }

    public function testGetThrowsForNonExistingClass(): void
    {
        try {
            $this->container->get(static::class . '123');
        } catch (EntryNotFoundException $e) {
            $this->assertSame(static::class . '123', $e->getMessage());
        }
    }

    public function testGetThrowsIfClassCannotBeConstructed(): void
    {
        $obj = new class (0) {
            public function __construct(public readonly int $i)
            {
            }
        };

        try {
            $this->container->get($obj::class);
        } catch (ContainerException $e) {
            $this->assertInstanceOf(ArgumentCountError::class, $e->getPrevious());
        }
    }
}
