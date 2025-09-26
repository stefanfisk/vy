<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;
use StefanFisk\Vy\Serialization\Html\HtmlableInterface;

use function count;
use function is_string;

final class EscapableRawTextElement extends BaseElement
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

        self::assertChildren($children);

        parent::__construct($type, $props, $key);
    }

    private static function assertChildren(mixed $children): void
    {
        if ($children === null) {
            return;
        }

        $children = Element::toChildArray($children);

        if ($children === []) {
            return;
        }

        if (count($children) > 1) {
            throw new InvalidArgumentException('Escapable raw text elements can only have a single child.');
        }

        $child = $children[0];

        if ($child === null) {
            return;
        }

        if (is_string($child)) {
            return;
        }

        if ($child instanceof HtmlableInterface) {
            return;
        }

        throw new InvalidArgumentException('Raw text elements child must be HtmlableInterface.');
    }

    public function __invoke(string | HtmlableInterface | null $text): VoidElement
    {
        $props = $this->props;

        $oldChildren = $props['children'] ?? null;

        if ($oldChildren !== null) {
            throw new InvalidArgumentException('Element already has children.');
        }

        $props['children'] = $text;

        return new VoidElement(
            key: $this->key,
            type: $this->type,
            props: $props,
        );
    }
}
