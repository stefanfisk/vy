<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(ContextHook::class)]
class ContextHookTest extends TestCase
{
    use MocksHookHandlerTrait;

    public function testUseCallsCurrentHandlerUseHook(): void
    {
        $ctx = Context::create('');

        $ret = new stdClass();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->once()
            ->with(ContextHook::class, $ctx)
            ->andReturn($ret);

        $this->assertSame(
            $ret,
            ContextHook::use($ctx),
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
