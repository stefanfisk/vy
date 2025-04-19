<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function count;

final class VoidElement extends BaseElement
{
    /**
     * @param non-empty-string|Closure $type
     * @param array<mixed> $props
     * @param ?non-empty-string $key
     */
    public static function create(string | Closure $type, array $props = [], ?string $key = null): self
    {
        return new self(
            type: $type,
            key: $key,
            props: $props,
        );
    }

    /**
     * @param non-empty-string|Closure $type
     * @param array<mixed> $props
     * @param ?non-empty-string $key
     */
    public function __construct(
        mixed $type,
        array $props = [],
        ?string $key = null,
    ) {
        $children = $props['children'] ?? null;

        if ($children !== null) {
            $children = Element::toChildArray($children);

            if (count($children) > 0) {
                throw new InvalidArgumentException('Void elements cannot have children.');
            }
        }

        parent::__construct($type, $props, $key);
    }
}
