<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\PhpReact\Hooks\ContextHook;
use StefanFisk\PhpReact\Tests\Support\FooContext;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\PhpReact\Tests\TestCase;
use stdClass;

#[CoversClass(ContextHook::class)]
class ContextHookTest extends TestCase
{
    use MocksHookHandlerTrait;

    public function testUseCallsCurrentHandlerUseHook(): void
    {
        $ret = new stdClass();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->once()
            ->with(ContextHook::class, FooContext::class)
            ->andReturn($ret);

        $this->assertSame(
            $ret,
            ContextHook::use(FooContext::class),
        );
    }

    public function testReturnsDefaultValueIfNoContextIsFound(): void
    {
        $this->markTestIncomplete();
    }

    public function testReturnsCurrentValueIfContextIsFound(): void
    {
        $this->markTestIncomplete();
    }

    public function testReturnsNewValueAfterChange(): void
    {
        $this->markTestIncomplete();
    }

    public function testCallsSubscribersForNewValue(): void
    {
        $this->markTestIncomplete();
    }

    public function testDoesNotCallSubscribersForSameValue(): void
    {
        $this->markTestIncomplete();
    }

    public function testDoesNotCallUnsubscribedForNewValue(): void
    {
        $this->markTestIncomplete();
    }
}
