<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Examples\ArticleCardGrid;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Html\img;

class MediaLibraryImg
{
    private const IMAGES = [
        1 => [
            'src' => 'https://picsum.photos/id/237/600/400',
            'alt' => 'Black labrador puppy',
        ],
        2 => [
            'src' => 'https://picsum.photos/id/1084/600/400',
            'alt' => 'A Huddle of Walruses',
        ],
        3 => [
            'src' => 'https://picsum.photos/id/200/600/400',
            'alt' => 'A long haired cow',
        ],
    ];

    public static function el(int $imageId, mixed $class = null): Element
    {
        return Element::create(self::render(...), [
            'imageId' => $imageId,
            'class' => $class,
        ]);
    }

    private static function render(int $imageId, mixed $class = null): mixed
    {
        $image = self::IMAGES[$imageId] ?? null;

        if ($image === null) {
            return null;
        }

        return img::el([
            'class' => $class,
            'src' => $image['src'],
            'alt' => $image['alt'],
        ]);
    }
}
