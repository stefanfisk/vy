<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Examples\ArticleCardGrid;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Html\a;
use StefanFisk\Vy\Elements\Html\div;
use StefanFisk\Vy\Elements\Html\p;

/**
 * @phpstan-type Article array{
 *     href: string,
 *     imageId: int<1,max>,
 *     title: string,
 *     description: string,
 *     tags: list<string>,
 * }
 */
class ArticleCard
{
    private const ARTICLES = [
        1 => [
            'href' => '#',
            'imageId' => 1,
            'title' => 'Labradors',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus quia, nulla! Maiores et perferendis eaque, exercitationem praesentium nihil.', // phpcs:ignore Generic.Files.LineLength.TooLong
            'tags' => [
                'photography',
                'labradors',
                'puppies',
            ],
        ],
        2 => [
            'href' => '#',
            'imageId' => 2,
            'title' => 'Walruses',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus quia, nulla! Maiores et perferendis eaque, exercitationem praesentium nihil.', // phpcs:ignore Generic.Files.LineLength.TooLong
            'tags' => [
                'photography',
                'walruses',
                'ocean',
            ],
        ],
        3 => [
            'href' => '#',
            'imageId' => 3,
            'title' => 'Long Haired Cows',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus quia, nulla! Maiores et perferendis eaque, exercitationem praesentium nihil.', // phpcs:ignore Generic.Files.LineLength.TooLong
            'tags' => [
                'photography',
                'cows',
                'plains',
            ],
        ],
    ];

    public static function el(int $articleId): Element
    {
        return Element::create(self::render(...), [
            'articleId' => $articleId,
        ]);
    }

    private static function render(
        int $articleId,
    ): mixed {
        $article = self::ARTICLES[$articleId] ?? null;

        if ($article === null) {
            return null;
        }

        return a::el(
            class: [
                'rounded',
                'bg-white',
                'overflow-hidden',
                'shadow-lg',
            ],
            href: $article['href'],
        )(
            MediaLibraryImg::el(
                class: 'w-full',
                imageId: $article['imageId'],
            ),
            self::contentEl(
                article: $article,
            ),
            Tags::el(
                tags: $article['tags'],
            ),
        );
    }

    /**
     * @param Article $article
     */
    private static function contentEl(array $article): Element
    {
        return Element::create(self::renderContent(...), [
            'article' => $article,
        ]);
    }

    /**
     * @param Article $article
     */
    private static function renderContent(array $article): mixed
    {
        return div::el([
            'px-6',
            'py-4',
        ])(
            div::el([
                'font-bold',
                'text-xl',
                'mb-2',
            ])(
                $article['title'],
            ),
            p::el([
                'text-gray-700',
                'text-base',
            ])(
                $article['description'],
            ),
        );
    }
}
