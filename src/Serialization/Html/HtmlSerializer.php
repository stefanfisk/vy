<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html;

use BackedEnum;
use Closure;
use Override;
use ReflectionFunction;
use StefanFisk\Vy\Context;
use StefanFisk\Vy\Errors\InvalidAttributeException;
use StefanFisk\Vy\Errors\InvalidChildValueException;
use StefanFisk\Vy\Errors\InvalidTagException;
use StefanFisk\Vy\Errors\RenderException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Serialization\Html\Transformers\AttributesTransformerInterface;
use StefanFisk\Vy\Serialization\Html\Transformers\ChildValueTransformerInterface;
use StefanFisk\Vy\Serialization\SerializerInterface;
use Throwable;

use function array_filter;
use function assert;
use function get_debug_type;
use function htmlentities;
use function is_float;
use function is_int;
use function is_scalar;
use function is_string;
use function lcfirst;
use function preg_match;
use function sprintf;
use function str_repeat;
use function str_starts_with;
use function strtr;
use function substr;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/** @implements SerializerInterface<string> */
final class HtmlSerializer implements SerializerInterface
{
    private const VOID_ELEMENTS = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'embed' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'link' => true,
        'meta' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];

    private const RAW_TEXT_ELEMENTS = [
        'iframe' => true,
        'noembed' => true,
        'noframes' => true,
        'plaintext' => true,
        'script' => true,
        'style' => true,
        'xmp' => true,
    ];

    /** @var array<AttributesTransformerInterface> */
    private readonly array $attributesTransformers;
    /** @var array<ChildValueTransformerInterface> */
    private readonly array $nodeValueTransformers;

    private int $componentDebugLevel = 0;

    private string $output = '';

    /** @param array<AttributesTransformerInterface|ChildValueTransformerInterface> $transformers */
    public function __construct(
        array $transformers,
        private readonly bool $encodeEntities = false,
        private readonly bool $debugComponents = false,
    ) {
        $this->attributesTransformers = array_filter(
            $transformers,
            fn ($m) => $m instanceof AttributesTransformerInterface,
        );
        $this->nodeValueTransformers = array_filter(
            $transformers,
            fn ($m) => $m instanceof ChildValueTransformerInterface,
        );
    }

    #[Override]
    public function serialize(Node $node): string
    {
        $this->output = '';

        $this->serializeNode($node, false);

        $output = $this->output;

        $this->output = '';

        return $output;
    }

    private function serializeNode(Node $node, bool $isSvgMode): void
    {
        assert($node->state === Node::STATE_NONE);

        if (is_string($node->type)) {
            $this->serializeTagNode($node, $isSvgMode);
        } else {
            $this->serializeComponent($node, $isSvgMode);
        }
    }

    private function serializeTagNode(Node $node, bool $isSvgMode): void
    {
        assert(is_string($node->type));

        $componentDebugLevel = $this->componentDebugLevel;
        $this->componentDebugLevel = 0;

        $name = $node->type;

        if ($this->isUnsafeName($name)) {
            throw new InvalidTagException(
                message: sprintf('%s is not a valid HTML tag name.', $name),
                node: $node,
            );
        }

        $isVoid = self::VOID_ELEMENTS[$name] ?? false;

        if ($isVoid && $node->children) {
            throw new InvalidTagException(
                message: sprintf('<%s> is a void element, and cannot have children.', $name),
                node: $node,
            );
        }

        $this->output .= '<';
        $this->output .= $name;

        $this->serializeAttributes($node);

        if ($isSvgMode && !$node->children) {
            $this->output .= ' />';

            return;
        }

        $this->output .= '>';

        $childSvgMode = $name === 'svg' || ($name !== 'foreignObject' && $isSvgMode);

        $this->serializeChildren($node, $childSvgMode);

        if ($isVoid) {
            return;
        }

        $this->output .= '</';
        $this->output .= $name;
        $this->output .= '>';

        $this->componentDebugLevel = $componentDebugLevel;
    }

    private function serializeAttributes(Node $node): void
    {
        assert($node->props !== null);
        $props = $node->props;
        unset($props['children']);

        $attributes = [];

        foreach ($props as $propName => $value) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (is_int($propName)) {
                if (!is_string($value)) {
                    /** @psalm-suppress NoValue,InvalidCast */
                    throw new InvalidAttributeException(
                        message: sprintf('Indexed property `%s` must be a string.', $propName),
                        node: $node,
                        name: (string) $propName,
                        value: $value,
                    );
                }

                $propName = $value;
                $value = true;
            }

            if ($propName === '') {
                throw new InvalidAttributeException(
                    message: 'Prop name cannot be empty string.',
                    node: $node,
                    name: $propName,
                    value: $value,
                );
            }

            $attributes[$propName] = $value;
        }

        try {
            $attributes = $this->applyAttributesTransformers($attributes);
        } catch (Throwable $e) {
            throw new RenderException(
                message: 'Failed to apply attribute transformer.',
                node: $node,
                previous: $e,
            );
        }

        foreach ($attributes as $attrName => $value) {
            if ($this->isUnsafeName($attrName)) {
                throw new InvalidAttributeException(
                    message: sprintf('`%s` is not a valid attribute name.', $attrName),
                    node: $node,
                    name: $attrName,
                    value: $value,
                );
            }

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            if ($value !== null && !is_scalar($value)) {
                throw new InvalidAttributeException(
                    message: sprintf(
                        'Attribute value is of type `%s`, expected (null|scalar).',
                        get_debug_type($value),
                    ),
                    node: $node,
                    name: $attrName,
                    value: $value,
                );
            }

            if ($value === null || $value === false) {
                continue;
            }

            $this->output .= ' ';
            $this->output .= $attrName;

            if ($value === true) {
                continue;
            }

            $this->output .= '="';
            $this->output .= $this->escapeAttribute((string) $value);
            $this->output .= '"';
        }
    }

    private function serializeValue(mixed $inValue, Node $parent, bool $isSvgMode): void
    {
        $this->componentDebugLevel = 0;

        try {
            $value = $this->applyChildValueTransformers($inValue);
        } catch (Throwable $e) {
            throw new InvalidChildValueException(
                message: 'Failed to apply child value transformer.',
                node: $parent,
                inValue: $inValue,
                value: null,
                previous: $e,
            );
        }

        if ($value === null) {
            return;
        }

        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            if (is_string($parent->type) && (self::RAW_TEXT_ELEMENTS[$parent->type] ?? false)) {
                throw new InvalidChildValueException(
                    message: sprintf(
                        '<%s> must only have HtmlableInterface children.',
                        $parent->type,
                    ),
                    node: $parent,
                    inValue: $inValue,
                    value: $value,
                );
            }

            $this->output .= $this->escapeText((string) $value);
        } elseif ($value instanceof HtmlableInterface) {
            $this->output .= $value->toHtml();
        } else {
            throw new InvalidChildValueException(
                message: sprintf(
                    'Node value is of type `%s`, expected (string|int|float|HtmlableInterface|null).',
                    get_debug_type($value),
                ),
                node: $parent,
                inValue: $inValue,
                value: $value,
            );
        }
    }

    private function serializeComponent(Node $node, bool $isSvgMode): void
    {
        $prettyType = null;
        $indent = '';

        if ($this->debugComponents) {
            $prettyType = $this->getPrettyComponentType($node->type);

            if ($prettyType !== null) {
                $indent = str_repeat('.', $this->componentDebugLevel);

                $this->output .= "<!--$indent<$prettyType>-->";
            }
        }

        $componentDebugLevel = $this->componentDebugLevel;
        $this->componentDebugLevel += 1;

        $this->serializeChildren($node, $isSvgMode);

        $this->componentDebugLevel = $componentDebugLevel;

        if ($this->debugComponents && $prettyType !== null) {
            $this->output .= "<!--$indent</$prettyType>-->";
        }
    }

    private function getPrettyComponentType(string | Closure $type): ?string
    {
        if (is_string($type)) {
            if ($type === '') {
                return null;
            }

            return $type;
        }

        $ref = new ReflectionFunction($type);
        $scopeClass = $ref->getClosureScopeClass()?->getName();

        if ($scopeClass === Context::class) {
            $scopeThis = $ref->getClosureThis();

            if ($scopeThis instanceof Context) {
                $scopeClass = "Context($scopeThis->name)";
            }
        }

        $name = $ref->getName();

        if ($name === 'render') {
            return $scopeClass;
        } elseif (str_starts_with($name, 'render')) {
            $name = lcfirst(substr($name, 6));
        }

        return "{$scopeClass}::{$name}";
    }

    private function serializeChildren(Node $parent, bool $isSvgMode): void
    {
        foreach ($parent->children as $child) {
            if ($child instanceof Node) {
                $this->serializeNode($child, $isSvgMode);
            } else {
                $this->serializeValue($child, $parent, $isSvgMode);
            }
        }
    }

    private function isUnsafeName(string $name): bool
    {
        return preg_match('/[\s\n\\/=\'"\0<>]/', $name) === 1;
    }

    /**
     * @param array<non-empty-string,mixed> $attributes
     *
     * @return array<non-empty-string,mixed>
     */
    private function applyAttributesTransformers(array $attributes): array
    {
        foreach ($this->attributesTransformers as $transformer) {
            $attributes = $transformer->processAttributes($attributes);
        }

        return $attributes;
    }

    private function applyChildValueTransformers(mixed $value): mixed
    {
        foreach ($this->nodeValueTransformers as $transformer) {
            $value = $transformer->processChildValue($value);
        }

        return $value;
    }

    /**
     * @see https://html.spec.whatwg.org/#escapingString
     */
    private function escapeText(string $value): string
    {
        if ($this->encodeEntities) {
            return $this->encode($value);
        }

        return strtr($value, [
            '&' => '&amp;',
            "\xc2\xa0" => '&nbsp;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]);
    }

    /**
     * @see https://html.spec.whatwg.org/#escapingString
     */
    private function escapeAttribute(string $value): string
    {
        if ($this->encodeEntities) {
            return $this->encode($value);
        }

        return strtr($value, [
            '&' => '&amp;',
            "\xc2\xa0" => '&nbsp;',
            '"' => '&quot;',
        ]);
    }

    private function encode(string $value): string
    {
        return htmlentities($value, ENT_HTML5 | ENT_SUBSTITUTE | ENT_QUOTES, 'UTF-8', true);
    }
}
