<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use Closure;
use InvalidArgumentException;
use StefanFisk\Vy\Serialization\Html\HtmlableInterface;

use function count;

final class RawTextElement extends BaseElement
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

            if (count($children) > 1) {
                throw new InvalidArgumentException('Raw text elements can only have a single child.');
            }

            if (count($children) === 1) {
                $child = $children[0];

                if (!$child instanceof HtmlableInterface) {
                    throw new InvalidArgumentException('Raw text elements child must be HtmlableInterface.');
                }
            }
        }

        parent::__construct($type, $props, $key);
    }

    public function __invoke(HtmlableInterface $text): VoidElement
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
