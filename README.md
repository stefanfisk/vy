<h1 align="center">stefanfisk/vy</h1>

<p align="center">
    <strong>A simple view library inspired by React.</strong>
</p>

<p align="center">
    <a href="https://github.com/stefanfisk/vy"><img src="https://img.shields.io/badge/source-stefanfisk/vy-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/stefanfisk/vy"><img src="https://img.shields.io/packagist/v/stefanfisk/vy.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/stefanfisk/vy.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/stefanfisk/vy/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/stefanfisk/vy.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <a href="https://github.com/stefanfisk/vy/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/actions/workflow/status/stefanfisk/vy/continuous-integration.yml?branch=main&style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/stefanfisk/vy"><img src="https://img.shields.io/codecov/c/gh/stefanfisk/vy?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
    <a href="https://shepherd.dev/github/stefanfisk/vy"><img src="https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fstefanfisk%2Fvy%2Fcoverage" alt="Psalm Type Coverage"></a>
</p>

## About

<!--
TODO: Use this space to provide more details about your package. Try to be
      concise. This is the introduction to your package. Let others know what
      your package does and how it can help them build applications.
-->




## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require stefanfisk/vy
```

## Usage

``` php

namespace Example;

use StefanFisk\Vy\Serialization\Html\UnsafeHtml;
use StefanFisk\Vy\Vy;

use function StefanFisk\Vy\el;

class Layout
{
    public function render(mixed $title, mixed $children): mixed
    {
        return el('', [], [
            UnsafeHtml::from('<!DOCTYPE html>'),
            el('html', [
                'lang'  => 'en',
                'class' => [
                    'h-full',
                ],
            ], [
                el('head', [], [
                    el('meta', ['charset' => 'UTF-8']),
                    el('meta', ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0']),
                    el('script', ['src' => 'https://cdn.tailwindcss.com']),
                    el('title', [], $title),
                ]),
                el('body', [
                    'class' => [
                        'max-w-screen-xl',
                        'mx-auto',
                        'p-8',
                        'bg-gray-100',
                    ],
                ], $children),
            ]),
        ]);
    }
}

class ArticleCard {
    private array $articles = [
        1 => [
            'href'        => '#',
            'imageId'     => 1,
            'title'       => 'Labradors',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus quia, nulla! Maiores et perferendis eaque, exercitationem praesentium nihil.',
            'tags'        => [
                'photography',
                'labradors',
                'puppies',
            ],
        ],
        2 => [
            'href'        => '#',
            'imageId'     => 2,
            'title'       => 'Walruses',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus quia, nulla! Maiores et perferendis eaque, exercitationem praesentium nihil.',
            'tags'        => [
                'photography',
                'walruses',
                'ocean',
            ],
        ],
        3 => [
            'href'        => '#',
            'imageId'     => 3,
            'title'       => 'Long Haired Cows',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus quia, nulla! Maiores et perferendis eaque, exercitationem praesentium nihil.',
            'tags'        => [
                'photography',
                'cows',
                'plains',
            ],
        ],
    ];

    public function render(
        int $articleId,
    ): mixed {
        $article = $this->articles[$articleId] ?? null;

        if (!$article) {
            return null;
        }

        return el('a', [
            'class' => [
                'rounded',
                'bg-white',
                'overflow-hidden',
                'shadow-lg',
            ],
            'href' => $article['href'],
        ], [
            el(Img::class, [
                'class' => 'w-full',
                'imageId' => $article['imageId'],
            ]),
            el('div', [
                'class' => [
                    'px-6',
                    'py-4',
                ],
            ], [
                el('div', [
                    'class' => [
                        'font-bold',
                        'text-xl',
                        'mb-2',
                    ],
                ], $article['title']),
                el('p', [
                    'class' => [
                        'text-gray-700',
                        'text-base',
                    ],
                ], $article['description']),
            ]),
            el(Tags::class, [
                'tags' => $article['tags'],
            ]),
        ]);
    }
}

class Img {
    private array $images = [
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

    public function render(int $imageId, mixed $class): mixed
    {
        $image = $this->images[$imageId] ?? null;

        if (!$image) {
            return null;
        }

        return el('img', [
            'class' => $class,
            'src' => $image['src'],
            'alt' => $image['alt'],
        ]);
    }
}

class Tags {
    public function render(array $tags): mixed
    {
        if (!$tags) {
            return null;
        }

        return el('div', [
            'class' => [
                'px-6',
                'pt-4',
                'pb-2',
            ],
        ], array_map(
            fn($tag) => el('span', [
                'class' => [
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
                ],
            ], "#$tag"),
            $tags,
        ));
    }
}

$el = el(Layout::class, [
    'title' => 'Vy Example',
], [
    el('div', [
        'class' => [
            'grid',
            'gap-8',
            'md:grid-cols-3',
        ],
    ], [
        el(ArticleCard::class, [
            'articleId' => 1,
        ]),
        el(ArticleCard::class, [
            'articleId' => 2,
        ]),
        el(ArticleCard::class, [
            'articleId' => 3,
        ]),
    ]),
]);

$vy = new Vy();

echo $vy->render($el);
```

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.






## Copyright and License

stefanfisk/vy is copyright © [Stefan Fisk](https://stefanfisk.com)
and licensed for use under the terms of the
MIT License (MIT). Please see [LICENSE](LICENSE) for more information.


