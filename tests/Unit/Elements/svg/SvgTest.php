<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Elements\svg;

use PHPUnit\Framework\Attributes\CoversNothing;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Svg\svg;
use StefanFisk\Vy\Tests\TestCase;

#[CoversNothing]
class SvgTest extends TestCase
{
    public function testKey(): void
    {
        $this->assertEquals(
            new Element(
                key: 'key',
                type: 'svg',
                props: [],
            ),
            svg::el(
                _key: 'key',
            ),
        );
    }

    public function testTransformsCamelCaseToKebabCase(): void
    {
        $this->assertEquals(
            new Element(
                key: null,
                type: 'svg',
                props: [
                    'data-foo' => 'bar',
                ],
            ),
            svg::el(
                dataFoo: 'bar',
            ),
        );
    }

    public function testDoesNotModifyKebabCaseProp(): void
    {
        $this->assertEquals(
            new Element(
                key: null,
                type: 'svg',
                props: [
                    'data-foo' => 'bar',
                ],
            ),
            svg::el(...[
                'data-foo' => 'bar',
            ]),
        );
    }

    public function testDoesNotModifyViewBoxProp(): void
    {
        $this->assertEquals(
            new Element(
                key: null,
                type: 'svg',
                props: [
                    'viewBox' => '0 0 1 1',
                ],
            ),
            svg::el(
                viewBox: '0 0 1 1',
            ),
        );
    }
}
