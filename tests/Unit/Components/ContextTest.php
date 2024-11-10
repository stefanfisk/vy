<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Components;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Hooks\ContextHook;
use StefanFisk\Vy\Hooks\ContextProviderHook;
use StefanFisk\Vy\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\Vy\Tests\Support\RendersComponentsTrait;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    use RendersComponentsTrait;
    use MocksHookHandlerTrait;
    use RendersComponentsTrait;

    public function testElCreatesElement(): void
    {
        $ctx = Context::create('');

        $el = $ctx->el(value: 'foo', key: 'key');

        $this->assertInstanceOf(Closure::class, $el->type);
        $this->assertEquals('key', $el->key);
        $this->assertEquals(['value' => 'foo'], $el->props);
    }

    public function testRenderReturnsChildren(): void
    {
        $ctx = Context::create('');

        $this->hookHandler
            ->shouldReceive('useHook')
            ->withAnyArgs()
            ->once()
            ->andReturn(null);

        $children = [
            'foo',
            new stdClass(),
        ];

        $el = $ctx->el('')($children);

        $this->assertSame(
            [$children],
            $this->renderComponent($el),
        );
    }

    public function testRenderReturnsNullForEmptyChildren(): void
    {
        $ctx = Context::create('');

        $this->hookHandler
            ->shouldReceive('useHook')
            ->withAnyArgs()
            ->once()
            ->andReturn(null);

        $this->assertNull($this->renderComponent($ctx->el('')));
    }

    public function testRenderCallsContextProviderHook(): void
    {
        /** @var Context<?stdClass> $ctx */
        $ctx = Context::create(null);

        $value = new stdClass();

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextProviderHook::class, $ctx, $value)
            ->once()
            ->andReturn(null);

        $this->renderComponent($ctx->el($value));
    }

    public function testUseCallsContextHookUse(): void
    {
        $ctx = Context::create('');

        $this->hookHandler
            ->shouldReceive('useHook')
            ->with(ContextHook::class, $ctx)
            ->once()
            ->andReturn(null);

        $ctx->use();
    }
}
