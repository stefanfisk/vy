<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Middleware;

use Closure;
use InvalidArgumentException;

use function array_filter;
use function array_map;
use function array_walk_recursive;
use function gettype;
use function implode;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

class StyleAttributeMiddleware implements HtmlAttributeValueMiddlewareInterface
{
    /** {@inheritDoc} */
    public function processAttributeValue(string $name, mixed $value, Closure $next): mixed
    {
        if ($name !== 'style') {
            return $next($value);
        }

        $value = $this->apply($value);

        return $next($value);
    }

    private function apply(mixed $styles): string
    {
        if (! $styles) {
            return '';
        }

        if (is_string($styles)) {
            return $styles;
        }

        if (! is_array($styles)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported type `%s`.',
                is_object($styles) ? $styles::class : gettype($styles),
            ));
        }

        // We wrap $effectiveStyles to make psalm happy.
        $wrapper = new class {
            /** @var array<string> */
            public array $effectiveStyles = [];
        };

        array_walk_recursive(
            $styles,
            function (mixed $value, int | string $key) use ($wrapper): void {
                if (!is_string($value)) {
                    throw new InvalidArgumentException(sprintf(
                        'Unsupported type `%s`.',
                        is_object($value) ? $value::class : gettype($value),
                    ));
                }

                if (is_int($key)) {
                    $style = $value;
                } else {
                    $style = "$key:$value";
                }

                $wrapper->effectiveStyles[] = $style;
            },
        );

        $effectiveStyles = array_map('trim', $wrapper->effectiveStyles);
        $effectiveStyles = array_filter($effectiveStyles);
        $effectiveStyles = implode(';', $effectiveStyles);

        return $effectiveStyles;
    }
}