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

Below is a minimal example. Check out the [examples](./examples/) for more.

```php

namespace StefanFisk\Vy\Example;

use StefanFisk\Vy\Serialization\Html\UnsafeHtml;
use StefanFisk\Vy\Vy;

use function StefanFisk\Vy\el;

class MyPage
{
    public static function el(string $title): Element
    {
        return Element::create(self::render(...), [
            'title' => $title,
        ]);
    }

    private static function render(string $title, mixed $children = null): mixed
    {
        return [
            UnsafeHtml::from('<!DOCTYPE html>'),
            html::el(lang: 'en')(
                head::el()(
                    meta::el(charset: 'UTF-8'),
                    title::el()($title),
                ),
                body::el()(
                    $children,
                ),
            ),
        ];
    }
}

$el = MyPage::el(
    title: 'Hello, world!',
)(
    h1::el()(
        'Hello, world!',
    ),
    p::el()(
        'This is a test page. There are many like it, but this one is mine.',
    ),
);

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


