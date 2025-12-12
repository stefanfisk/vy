<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Html\div;
use StefanFisk\Vy\HasStaticContextTrait;
use StefanFisk\Vy\Tests\TestCase;
use StefanFisk\Vy\Vy;

#[CoversClass(HasStaticContextTrait::class)]
class HasStaticContextTraitTest extends TestCase
{
    public function testEl(): void
    {
        $c = new class {
            /** @use HasStaticContextTrait<string> */
            use HasStaticContextTrait;

            public static function el(string $value): Element
            {
                return Element::create(self::render(...), [
                    'value' => $value,
                ]);
            }

            private static function render(string $value): mixed
            {
                return self::contextEl($value)(
                    self::innerEl(),
                );
            }

            public static function innerEl(): Element
            {
                return Element::create(self::renderInner(...));
            }

            private static function renderInner(): mixed
            {
                $value = self::useContext();

                return div::el()(
                    $value,
                );
            }
        };

        $vy = new Vy();

        $html = $vy->render($c::el('Foo'));

        self::assertSame('<div>Foo</div>', $html);
    }
}
