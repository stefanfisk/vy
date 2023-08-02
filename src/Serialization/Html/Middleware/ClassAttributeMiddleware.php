<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Middleware;

use Closure;
use InvalidArgumentException;

use function array_filter;
use function array_map;
use function array_push;
use function array_walk_recursive;
use function explode;
use function gettype;
use function implode;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function sort;
use function sprintf;

class ClassAttributeMiddleware implements HtmlAttributeValueMiddlewareInterface
{
    /** {@inheritDoc} */
    public function processAttributeValue(string $name, mixed $value, Closure $next): mixed
    {
        if ($name !== 'class') {
            return $next($value);
        }

        $value = $this->apply($value);

        return $next($value);
    }

    private function apply(mixed $classes): string | null
    {
        if (! $classes) {
            return null;
        }

        if (is_string($classes)) {
            $classes = array_filter(explode(' ', $classes));
        }

        if (! is_array($classes)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported type `%s`.',
                is_object($classes) ? $classes::class : gettype($classes),
            ));
        }

        // We wrap $effectiveClasses to make psalm happy.
        $wrapper = new class {
            /** @var array<string> */
            public array $effectiveClasses = [];
        };

        array_walk_recursive(
            $classes,
            static function (mixed $value, int | string $key) use ($wrapper): void {
                if (is_int($key)) {
                    $conditional = true;
                    $class = $value;
                } else {
                    $conditional = $value;
                    $class = $key;
                }

                if (! $conditional || ! $class) {
                    return;
                }

                if (!is_string($class)) {
                    throw new InvalidArgumentException(sprintf(
                        'Unsupported type `%s`.',
                        is_object($class) ? $class::class : gettype($class),
                    ));
                }

                $class = explode(' ', $class);

                array_push($wrapper->effectiveClasses, ...$class);
            },
        );

        $effectiveClasses = array_map('trim', $wrapper->effectiveClasses);
        $effectiveClasses = array_filter($effectiveClasses);

        sort($effectiveClasses);

        return implode(' ', $effectiveClasses) ?: null;
    }
}
