<?php

/**
 * This file is part of stefanfisk/phpreact
 *
 * @copyright Copyright (c) Stefan Fisk <contact@stefanfisk.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace StefanFisk\PhpReact;

/**
 * An example class to act as a starting point for developing your library
 */
class Example
{
    /**
     * Returns a greeting statement using the provided name
     */
    public function greet(string $name = 'World'): string
    {
        return "Hello, {$name}!";
    }
}
