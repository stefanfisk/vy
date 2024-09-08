<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function array_merge;
use function array_reduce;
use function is_array;
use function is_bool;

class Element
{
    /** @return list<mixed> */
    public static function toChildArray(mixed $renderChildren): array
    {
        // Flatten the render children and remove empty items

        if (! is_array($renderChildren)) {
            $renderChildren = [$renderChildren];
        }

        return array_reduce(
            $renderChildren,
            /** @param list<mixed> $carry */
            function (array $carry, mixed $el) {
                if (is_array($el)) {
                    return array_merge($carry, self::toChildArray($el));
                }

                if ($el === null || is_bool($el) || $el === '') {
                    return $carry;
                }

                $carry[] = $el;

                return $carry;
            },
            [],
        );
    }

    /**
     * @param string|Context<mixed>|Closure(TProps):mixed $type
     * @param TProps $props
     *
     * @template TProps of array
     */
    public function __construct(
        public readonly string | Context | Closure $type,
        public readonly array $props,
    ) {
    }

    public function __invoke(mixed ...$children): Element
    {
        if (isset($this->props['children'])) {
            throw new InvalidArgumentException('Element already has children.');
        }

        $newProps = $this->props;

        $newProps['children'] = $children;

        return new Element($this->type, $newProps);
    }
}
