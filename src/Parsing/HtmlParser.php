<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Parsing;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMDocumentFragment;
use DOMDocumentType;
use DOMElement;
use DOMNameSpaceNode;
use DOMNode;
use DOMText;
use DOMXPath;
use Masterminds\HTML5;
use Masterminds\HTML5\Elements;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Serialization\Html\UnsafeHtml;

use function assert;
use function in_array;
use function is_string;

final class HtmlParser
{
     /**
     * Defined in http://www.w3.org/TR/html51/infrastructure.html#html-namespace-0.
     */
    private const NAMESPACE_HTML = 'http://www.w3.org/1999/xhtml';
    private const NAMESPACE_MATHML = 'http://www.w3.org/1998/Math/MathML';
    private const NAMESPACE_SVG = 'http://www.w3.org/2000/svg';
    private const NAMESPACE_XML = 'http://www.w3.org/XML/1998/namespace';
    private const NAMESPACE_XMLNS = 'http://www.w3.org/2000/xmlns/';

    /** @var list<string> */
    private array $implicitNamespaces = [
        self::NAMESPACE_HTML,
        self::NAMESPACE_SVG,
        self::NAMESPACE_MATHML,
        self::NAMESPACE_XML,
        self::NAMESPACE_XMLNS,
    ];

    /** @var list<array{nodeNamespace:string,xpath?:string,attrName?:list<string>}> */
    private array $nonBooleanAttributes = [
        [
            'nodeNamespace' => 'http://www.w3.org/1999/xhtml',
            'attrName' => [
                'href',
                'hreflang',
                'http-equiv',
                'icon',
                'id',
                'keytype',
                'kind',
                'label',
                'lang',
                'language',
                'list',
                'maxlength',
                'media',
                'method',
                'name',
                'placeholder',
                'rel',
                'rows',
                'rowspan',
                'sandbox',
                'spellcheck',
                'scope',
                'seamless',
                'shape',
                'size',
                'sizes',
                'span',
                'src',
                'srcdoc',
                'srclang',
                'srcset',
                'start',
                'step',
                'style',
                'summary',
                'tabindex',
                'target',
                'title',
                'type',
                'value',
                'width',
                'border',
                'charset',
                'cite',
                'class',
                'code',
                'codebase',
                'color',
                'cols',
                'colspan',
                'content',
                'coords',
                'data',
                'datetime',
                'default',
                'dir',
                'dirname',
                'enctype',
                'for',
                'form',
                'formaction',
                'headers',
                'height',
                'accept',
                'accept-charset',
                'accesskey',
                'action',
                'align',
                'alt',
                'bgcolor',
            ],
        ],
        [
            'nodeNamespace' => 'http://www.w3.org/1999/xhtml',
            'xpath' => 'starts-with(local-name(), \'data-\')',
        ],
    ];

    private ?DOMXPath $xpath = null;

    public function __construct(private readonly HTML5 $html5 = new HTML5())
    {
    }

    /** @param array<string,mixed> $options */
    public function parseDocument(string $input, array $options = []): mixed
    {
        $node = $this->html5->parse($input, $options);

        $this->xpath = new DOMXPath($node);

        return $this->mapNode($node);
    }

    /** @param array<string,mixed> $options */
    public function parseFragment(string $input, array $options = []): mixed
    {
        $node = $this->html5->parseFragment($input, $options);

        return $this->mapNode($node);
    }

    private function mapNode(DOMNode $node): mixed
    {
        return match (true) {
            $node instanceof DOMDocumentType => $this->mapDocumentTypeNode($node),
            $node instanceof DOMDocument => $this->mapDocumentNode($node),
            $node instanceof DOMElement => $this->mapElementNode($node),
            $node instanceof DOMText => $this->mapTextNode($node),
            $node instanceof DOMDocumentFragment => $this->mapFragmentNode($node),
            $node instanceof DOMComment => $this->mapCommentNode($node),
            default => $this->mapUnknownDomNode($node),
        };
    }

    private function mapDocumentTypeNode(DOMDocumentType $node): mixed
    {
        return UnsafeHtml::from("<!DOCTYPE html>\n");
    }

    private function mapDocumentNode(DOMDocument $node): mixed
    {
        return new Element(
            type: '',
            props: [
                'children' => [
                    $this->mapChildNodes($node),
                    "\n",
                ],
            ],
        );
    }

    private function mapElementNode(DOMElement $node): mixed
    {
        $props = [];
        /** @var DOMAttr $attrNode */
        foreach ($node->attributes ?: [] as $attrNode) { // @phpstan-ignore-line
            $value = $attrNode->nodeValue;

            if ($value === '' && !$this->isNonBooleanAttribute($attrNode)) {
                $value = true;
            }

            $props[$attrNode->nodeName] = $value;
        }

        assert($this->xpath !== null);
        foreach ($this->xpath->query('namespace::*[not(.=../../namespace::*)]', $node) ?: [] as $nsNode) {
            if (!$nsNode instanceof DOMNameSpaceNode) {
                continue;
            }

            if (!in_array($nsNode->nodeValue, $this->implicitNamespaces, true)) {
                /** @psalm-suppress MixedArrayOffset */
                $props[$nsNode->nodeName] = $nsNode->nodeValue;
            }
        }
        $props['children'] = $this->mapChildNodes($node);

        return new Element(
            type: $node->tagName,
            props: $props,
        );
    }

    private function isNonBooleanAttribute(DOMAttr $attr): bool
    {
        $ele = $attr->ownerElement;
        assert($ele instanceof DOMElement);

        foreach ($this->nonBooleanAttributes as $rule) {
            if (isset($rule['nodeNamespace']) && $rule['nodeNamespace'] !== $ele->namespaceURI) {
                continue;
            }
            if (isset($rule['attrName']) && !in_array($attr->localName, $rule['attrName'], true)) {
                continue;
            }
            if (isset($rule['xpath'])) {
                assert($this->xpath !== null);

                if (!$this->xpath->evaluate($rule['xpath'], $attr)) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    private function mapTextNode(DOMText $node): mixed
    {
        $parentLocalName = $node->parentNode?->localName;

        if (is_string($parentLocalName) && Elements::isA($parentLocalName, Elements::TEXT_RAW)) {
            return UnsafeHtml::from($node->data);
        } else {
            return $node->data;
        }
    }

    private function mapFragmentNode(DOMDocumentFragment $node): Element
    {
        return new Element(
            type: '',
            props: [
                'children' => $this->mapChildNodes($node),
            ],
        );
    }

    private function mapCommentNode(DOMComment $node): mixed
    {
        return UnsafeHtml::from("<!--$node->data-->");
    }

    private function mapUnknownDomNode(DOMNode $node): mixed
    {
        return null;
    }

    /** @return list<mixed> */
    private function mapChildNodes(DOMNode $node): array
    {
        $childEls = [];

        /** @var DOMNode $childNode */
        foreach ($node->childNodes as $childNode) {
            $childEls[] = $this->mapNode($childNode);
        }

        return $childEls;
    }
}
