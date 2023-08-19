<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html;

use StefanFisk\PhpReact\Errors\InvalidAttributeException;
use StefanFisk\PhpReact\Errors\InvalidNodeValueException;
use StefanFisk\PhpReact\Errors\InvalidTagException;
use StefanFisk\PhpReact\Rendering\Node;
use StefanFisk\PhpReact\Serialization\Html\Middleware\HtmlAttributeValueMiddlewareInterface;
use StefanFisk\PhpReact\Serialization\Html\Middleware\HtmlNodeValueMiddlewareInterface;
use StefanFisk\PhpReact\Serialization\SerializerInterface;
use StefanFisk\PhpReact\Support\HtmlableInterface;
use Throwable;

use function array_filter;
use function array_reverse;
use function assert;
use function gettype;
use function is_float;
use function is_int;
use function is_object;
use function is_scalar;
use function is_string;
use function preg_match;
use function sprintf;
use function strtr;

/** @implements SerializerInterface<string> */
class HtmlSerializer implements SerializerInterface
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

    /** @var array<HtmlAttributeValueMiddlewareInterface> */
    private readonly array $attributeValueMiddleware;
    /** @var array<HtmlNodeValueMiddlewareInterface> */
    private readonly array $nodeValueMiddleware;

    private string $output = '';

    /** @param array<HtmlAttributeValueMiddlewareInterface|HtmlNodeValueMiddlewareInterface> $middlewares */
    public function __construct(
        array $middlewares,
    ) {
        $this->attributeValueMiddleware = array_filter(
            $middlewares,
            fn ($m) => $m instanceof HtmlAttributeValueMiddlewareInterface,
        );
        $this->nodeValueMiddleware = array_filter(
            $middlewares,
            fn ($m) => $m instanceof HtmlNodeValueMiddlewareInterface,
        );
    }

    public function serialize(Node $node): string
    {
        $this->output = '';

        $this->serializeNode($node);

        $output = $this->output;

        $this->output = '';

        return $output;
    }

    private function serializeChild(mixed $child, Node $parent): void
    {
        if ($child instanceof Node) {
            $this->serializeNode($child);
        } else {
            $this->serializeValue($child, $parent);
        }
    }

    private function serializeNode(Node $node): void
    {
        assert($node->state === Node::STATE_NONE);

        if (is_string($node->type) && !$node->component) {
            $this->serializeTagNode($node);
        } else {
            $this->serializeChildren($node->children, $node);
        }
    }

    private function serializeTagNode(Node $node): void
    {
        assert(is_string($node->type));

        $name = $node->type;

        if ($name === '') {
            throw new InvalidTagException(
                message: 'HTML tag cannot be empty string.',
                node: $node,
            );
        }

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

        assert($node->props !== null);
        $atts = $node->props;
        unset($atts['children']);

        foreach ($atts as $attName => $attValue) {
            if (is_int($attName)) {
                if (!is_string($attValue)) {
                    throw new InvalidAttributeException(
                        message: sprintf('Indexed attribute `%s` must be a string.', $attName),
                        node: $node,
                        name: (string) $attName,
                        value: $attValue,
                    );
                }

                $attName = $attValue;
                $attValue = true;
            }

            if ($attName === '' || $this->isUnsafeName($attName)) {
                throw new InvalidAttributeException(
                    message: sprintf('`%s` is not a valid attribute name.', $name),
                    node: $node,
                    name: $attName,
                    value: $attValue,
                );
            }

            try {
                $attValue = $this->applyAttributeValueMiddleware($attName, $attValue);
            } catch (Throwable $e) {
                throw new InvalidAttributeException(
                    message: 'Failed to apply attribute middleware.',
                    node: $node,
                    name: $attName,
                    value: $attValue,
                    previous: $e,
                );
            }

            if ($attValue !== null && !is_scalar($attValue)) {
                throw new InvalidAttributeException(
                    message: sprintf(
                        'Attribute value is of type `%s`, expected (null|scalar).',
                        is_object($attValue) ? $attValue::class : gettype($attValue),
                    ),
                    node: $node,
                    name: $attName,
                    value: $attValue,
                );
            }

            if ($attValue === null || $attValue === false) {
                continue;
            }

            $this->output .= ' ';
            $this->output .= $attName;

            if ($attValue === true) {
                continue;
            }

            $this->output .= '="';
            $this->output .= $this->escapeAttribute((string) $attValue);
            $this->output .= '"';
        }

        $this->output .= '>';

        $this->serializeChildren($node->children, $node);

        if ($isVoid) {
            return;
        }

        $this->output .= '</';
        $this->output .= $name;
        $this->output .= '>';
    }

    private function serializeValue(mixed $inValue, Node $parent): void
    {
        try {
            $value = $this->applyNodeValueMiddleware($inValue);
        } catch (Throwable $e) {
            throw new InvalidNodeValueException(
                message: 'Failed to apply node value middleware.',
                node: $parent,
                inValue: $inValue,
                value: null,
                previous: $e,
            );
        }

        if ($value === null) {
            return;
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            if (is_string($parent->type) && (self::RAW_TEXT_ELEMENTS[$parent->type] ?? false)) {
                throw new InvalidNodeValueException(
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
            throw new InvalidNodeValueException(
                message: sprintf(
                    'Node value is of type `%s`, expected (string|int|float|HtmlableInterface|null).',
                    is_object($value) ? $value::class : gettype($value),
                ),
                node: $parent,
                inValue: $inValue,
                value: $value,
            );
        }
    }

    /** @param list<mixed> $children */
    private function serializeChildren(array $children, Node $parent): void
    {
        foreach ($children as $child) {
             $this->serializeChild($child, $parent);
        }
    }

    private function isUnsafeName(string $name): bool
    {
        return preg_match('/[\s\n\\/=\'"\0<>]/', $name) === 1;
    }

    private function applyAttributeValueMiddleware(string $name, mixed $value): mixed
    {
        $next = fn (mixed $value): mixed => $value;

        foreach (array_reverse($this->attributeValueMiddleware) as $middleware) {
            $next = fn (mixed $value): mixed => $middleware->processAttributeValue($name, $value, $next);
        }

        return $next($value);
    }

    private function applyNodeValueMiddleware(mixed $value): mixed
    {
        $next = fn (mixed $value): mixed => $value;

        foreach (array_reverse($this->nodeValueMiddleware) as $middleware) {
            $next = fn (mixed $value): mixed => $middleware->processNodeValue($value, $next);
        }

        return $next($value);
    }

    /**
     * @see https://html.spec.whatwg.org/#escapingString
     */
    private function escapeText(string $value): string
    {
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
        return strtr($value, [
            '&' => '&amp;',
            "\xc2\xa0" => '&nbsp;',
            '"' => '&quot;',
        ]);
    }
}
