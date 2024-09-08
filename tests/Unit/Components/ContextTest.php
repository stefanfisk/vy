<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Unit\Components;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Context;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    use MocksHookHandlerTrait;

    public function testCreateReturnsInstance(): void
    {
        $value = new stdClass();

        $ctx = Context::create($value);

        $this->assertSame($value, $ctx->defaultValue);
    }

    public function testUseCallsHandler(): void
    {
        $value = new stdClass();

        $ctx = Context::create($value);

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextHook::class, $ctx)
            ->once()
            ->andReturn(null);

        $ctx->use();
    }
}
