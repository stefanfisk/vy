<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Examples\ArticleCardGrid;

use StefanFisk\Vy\Elements\Html\div;
use StefanFisk\Vy\Elements\Html\span;
use StefanFisk\Vy\VoidElement;

use function array_map;

class Tags
{
    /**
     * @param array<non-empty-string> $tags
     */
    public static function el(array $tags): VoidElement
    {
        return new VoidElement(self::render(...), [
            'tags' => $tags,
        ]);
    }

    /**
     * @param array<non-empty-string> $tags
     */
    private static function render(array $tags): mixed
    {
        if ($tags === []) {
            return null;
        }

        return div::el([
            'px-6',
            'pt-4',
            'pb-2',
        ])(
            array_map(
                fn ($tag) => span::el([
                    'inline-block',
                    'bg-gray-200',
                    'rounded-full',
                    'px-3',
                    'py-1',
                    'text-sm',
                    'font-semibold',
                    'text-gray-700',
                    'mr-2',
                    'mb-2',
                ])(
                    "#{$tag}",
                ),
                $tags,
            ),
        );
    }
}
