<?php

declare(strict_types=1);

namespace StefanFisk\Vy;

use InvalidArgumentException;
use StefanFisk\Vy\Serialization\Html\HtmlableInterface;

class RawTextElement extends BaseElement
{
    public function __invoke(HtmlableInterface $text): BaseElement
    {
        $props = $this->props;

        $oldChildren = $props['children'] ?? null;

        if ($oldChildren !== null) {
            throw new InvalidArgumentException('Element already has children.');
        }

        $props['children'] = $text;

        return new Element(
            key: $this->key,
            type: $this->type,
            props: $props,
        );
    }
}
