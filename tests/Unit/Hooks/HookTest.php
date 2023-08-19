<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use StefanFisk\PhpReact\Hooks\Hook;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksHookHandlerTrait;
use StefanFisk\PhpReact\Tests\Support\Mocks\MocksRendererTrait;
use stdClass;

#[CoversClass(Hook::class)]
class HookTest extends TestCase
{
    use MocksHookHandlerTrait;
    use MocksRendererTrait;

    public function testUseWithCallsCurrentRendererUseHook(): void
    {
        $node = new Node(
            id: -1,
            parent: null,
            key: null,
            type: null,
            component: null,
        );
        $arg0 = 'foo';
        $arg1 = new stdClass();
        $ret = new stdClass();

        $hook = new class (renderer: $this->renderer, node: $node) extends Hook {
            public static function use(string $str, object $obj): object
            {
                return (object) static::useWith($str, $obj);
            }

            public function initialRender(mixed ...$args): mixed
            {
                throw new RuntimeException(__FUNCTION__ . ' must be mocked.');
            }

            public function rerender(mixed ...$args): mixed
            {
                throw new RuntimeException(__FUNCTION__ . ' must be mocked.');
            }
        };

        $this->hookHandler
            ->expects($this->once())
            ->method('useHook')
            ->with($hook::class, $arg0, $arg1)
            ->willReturn($ret);

        $this->assertSame(
            $ret,
            $hook::use($arg0, $arg1),
        );
    }
}
