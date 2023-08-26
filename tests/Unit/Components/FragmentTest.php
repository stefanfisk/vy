<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Unit\Components;

use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Components\Fragment;
use StefanFisk\Vy\Tests\TestCase;
use stdClass;

#[CoversClass(Fragment::class)]
class FragmentTest extends TestCase
{
    public function testRendersChildren(): void
    {
        $fragment = new Fragment();

        $children = [
            'foo' => 'bar',
            new stdClass(),
        ];

        $this->assertSame(
            $children,
            $fragment->render(children: $children),
        );
    }

    public function testRendersEmptyProps(): void
    {
        $fragment = new Fragment();

        $this->assertNull($fragment->render());
    }

    public function testThrowsForNonChildrenProps(): void
    {
        $fragment = new Fragment();

        $this->expectException(Error::class);

        // @phpstan-ignore-next-line
        $fragment->render(...['foo' => 'bar']);
    }
}
