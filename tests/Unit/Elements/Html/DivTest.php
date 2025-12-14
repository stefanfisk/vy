<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit\Serialization\Html;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Html\div;
use StefanFisk\Vy\Tests\TestCase;

#[CoversClass(div::class)]
class DivTest extends TestCase
{
    public function testDoesNotMergesClassIntoPropsIfNotPassed(): void
    {
        $this->assertEquals(
            new Element(
                type: 'div',
                props: [
                    'name' => 'value',
                ],
            ),
            div::el([
                'name' => 'value',
            ]),
        );
    }

    public function testPassesKey(): void
    {
        $this->assertEquals(
            new Element(
                key: 'key',
                type: 'div',
                props: [],
            ),
            div::el([
                Element::KEY => 'key',
            ]),
        );
    }
}
