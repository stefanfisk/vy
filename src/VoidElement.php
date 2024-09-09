<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;

use function count;

class VoidElement extends BaseElement
{
    /**
     * @param ?non-empty-string $key
     * @param array<mixed> $props
     */
    public function __construct(
        string | Closure $type,
        ?string $key = null,
        array $props = [],
    ) {
        $children = $props['children'] ?? null;

        if ($children !== null) {
            $children = Element::toChildArray($children);

            if (count($children) > 0) {
                throw new InvalidArgumentException('Void elements cannot have children.');
            }
        }

        parent::__construct($type, $key, $props);
    }
}
