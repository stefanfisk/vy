<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Unit\Components;

use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StefanFisk\PhpReact\Components\Fragment;
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
