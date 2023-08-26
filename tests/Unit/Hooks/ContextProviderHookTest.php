<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Tests\Support\FooContext;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(ContextHook::class)]
class ContextProviderHookTest extends TestCase
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
