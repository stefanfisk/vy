<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Examples\ArticleCardGrid;

use StefanFisk\Vy\Element;
use StefanFisk\Vy\Elements\Html\body;
use StefanFisk\Vy\Elements\Html\head;
use StefanFisk\Vy\Elements\Html\html;
use StefanFisk\Vy\Elements\Html\meta;
use StefanFisk\Vy\Elements\Html\script;
use StefanFisk\Vy\Elements\Html\title;
use StefanFisk\Vy\Serialization\Html\UnsafeHtml;

class Layout
{
    public static function el(?string $title = null): Element
    {
        return Element::create(self::render(...), [
            'title' => $title,
        ]);
    }

    private static function render(?string $title = null, mixed $children = null): mixed
    {
        return [
            UnsafeHtml::from('<!DOCTYPE html>'),
            html::el(
                lang: 'en',
                class: [
                    'h-full',
                ],
            )(
                head::el()(
                    meta::el(charset: 'UTF-8'),
                    meta::el(name: 'viewport', content: 'width=device-width, initial-scale=1.0'),
                    script::el(src: 'https://cdn.tailwindcss.com'),
                    title::el()($title),
                ),
                body::el([
                    'max-w-screen-xl',
                    'mx-auto',
                    'p-8',
                    'bg-gray-100',
                ])(
                    $children,
                ),
            ),
        ];
    }
}
