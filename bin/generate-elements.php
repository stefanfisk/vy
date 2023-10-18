#!/bin/env php
<?php // phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace StefanFisk\Vy;

use StefanFisk\Vy\Elements\Utils;

use function array_unique;
use function array_unshift;
use function assert;
use function file_exists;
use function file_put_contents;
use function glob;
use function in_array;
use function is_array;
use function is_dir;
use function is_link;
use function is_string;
use function mkdir;
use function preg_replace_callback;
use function rmdir;
use function strtoupper;
use function unlink;

use const GLOB_ONLYDIR;

require __DIR__ . '/../vendor/autoload.php';

$basePath = __DIR__ . '/../src/Elements';

// Tag defs

/** @var list<string> $htmlTagNames */
$htmlTagNames = [
    'a',
    'abbr',
    'address',
    'area',
    'article',
    'aside',
    'audio',
    'b',
    'base',
    'bdi',
    'bdo',
    'blockquote',
    'body',
    'br',
    'button',
    'canvas',
    'caption',
    'cite',
    'code',
    'col',
    'colgroup',
    'data',
    'datalist',
    'dd',
    'del',
    'details',
    'dfn',
    'dialog',
    'div',
    'dl',
    'dt',
    'em',
    'embed',
    'fieldset',
    'figcaption',
    'figure',
    'footer',
    'form',
    'h1',
    'head',
    'header',
    'hgroup',
    'hr',
    'html',
    'i',
    'iframe',
    'img',
    'input',
    'ins',
    'kbd',
    'label',
    'legend',
    'li',
    'link',
    'main',
    'map',
    'mark',
    'menu',
    'meta',
    'meter',
    'nav',
    'noscript',
    'object',
    'ol',
    'optgroup',
    'option',
    'output',
    'p',
    'picture',
    'pre',
    'progress',
    'q',
    'rp',
    'rt',
    'ruby',
    's',
    'samp',
    'script',
    'search',
    'section',
    'select',
    'slot',
    'small',
    'source',
    'span',
    'strong',
    'style',
    'sub',
    'summary',
    'sup',
    'table',
    'tbody',
    'td',
    'template',
    'textarea',
    'tfoot',
    'th',
    'thead',
    'time',
    'title',
    'tr',
    'track',
    'u',
    'ul',
    'var',
    'video',
    'wbr',
];

/** @var list<string> $svgTagNames */
$svgTagNames = [
    'a',
    'animate',
    'animateMotion',
    'animateTransform',
    'circle',
    'clipPath',
    'defs',
    'desc',
    'ellipse',
    'feBlend',
    'feColorMatrix',
    'feComponentTransfer',
    'feComposite',
    'feConvolveMatrix',
    'feDiffuseLighting',
    'feDisplacementMap',
    'feDistantLight',
    'feDropShadow',
    'feFlood',
    'feFuncA',
    'feFuncB',
    'feFuncG',
    'feFuncR',
    'feGaussianBlur',
    'feImage',
    'feMerge',
    'feMergeNode',
    'feMorphology',
    'feOffset',
    'fePointLight',
    'feSpecularLighting',
    'feSpotLight',
    'feTile',
    'feTurbulence',
    'filter',
    'foreignObject',
    'g',
    'image',
    'line',
    'linearGradient',
    'marker',
    'mask',
    'metadata',
    'mpath',
    'path',
    'pattern',
    'polygon',
    'polyline',
    'radialGradient',
    'rect',
    'script',
    'set',
    'stop',
    'style',
    'svg',
    'switch',
    'symbol',
    'text',
    'textPath',
    'title',
    'tspan',
    'use',
    'view',
];

/** @var array<string,list<string>> $svgAttributeNameToTagNames */
$svgAttributeNameToTagNames = [
    'baseFrequency' => [
        'feTurbulence',
    ],
    'calcMode' => [
        'animate',
        'animateMotion',
        'animateTransform',
    ],
    'clipPathUnits' => [
        'clipPath',
    ],
    'diffuseConstant' => [
        'feDiffuseLighting',
    ],
    'edgeMode' => [
        'feConvolveMatrix',
        'feGaussianBlur',
    ],
    'filterUnits' => [
        'filter',
    ],
    'kernelMatrix' => [
        'feConvolveMatrix',
    ],
    'keyPoints' => [
        'animate',
        'animateMotion',
        'animateTransform',
        'set',
    ],
    'keySplines' => [
        'animate',
        'animateMotion',
        'animateTransform',
    ],
    'keyTimes' => [
        'animate',
        'animateMotion',
        'animateTransform',
    ],
    'lengthAdjust' => [
        'text',
        'textPath',
        'tref',
        'tspan',
    ],
    'limitingConeAngle' => [
        'feSpotLight',
    ],
    'markerHeight' => [
        'marker',
    ],
    'markerUnits' => [
        'marker',
    ],
    'markerWidth' => [
        'marker',
    ],
    'maskContentUnits' => [
        'mask',
    ],
    'maskUnits' => [
        'mask',
    ],
    'numOctaves' => [
        'feTurbulence',
    ],
    'pathLength' => [
        'circle',
        'ellipse',
        'line',
        'path',
        'polygon',
        'polyline',
        'rect',
    ],
    'patternContentUnits' => [
        'pattern',
    ],
    'patternTransform' => [
        'pattern',
    ],
    'patternUnits' => [
        'pattern',
    ],
    'pointsAtX' => [
        'feSpotLight',
    ],
    'pointsAtY' => [
        'feSpotLight',
    ],
    'pointsAtZ' => [
        'feSpotLight',
    ],
    'preserveAlpha' => [
        'feConvolveMatrix',
    ],
    'preserveAspectRatio' => [
        'svg',
        'symbol',
        'image',
        'feImage',
        'marker',
        'pattern',
        'view',
    ],
    'primitiveUnits' => [
        'filter',
    ],
    'refX' => [
        'marker',
        'symbol',
    ],
    'refY' => [
        'marker',
        'symbol',
    ],
    'repeatCount' => [
        'animate',
        'animateMotion',
        'animateTransform',
        'set',
    ],
    'repeatDur' => [
        'animate',
        'animateMotion',
        'animateTransform',
        'set',
    ],
    'specularConstant' => [
        'feSpecularLighting',
    ],
    'specularExponent' => [
        'feSpecularLighting',
        'feSpotLight',
    ],
    'spreadMethod' => [
        'linearGradient',
        'radialGradient',
    ],
    'startOffset' => [
        'textPath',
    ],
    'stdDeviation' => [
        'feGaussianBlur',
    ],
    'stitchTiles' => [
        'feTurbulence',
    ],
    'surfaceScale' => [
        'feDiffuseLighting',
        'feSpecularLighting',
    ],
    'systemLanguage' => [
        'a',
        'animate',
        'animateMotion',
        'animateTransform',
        'circle',
        'clipPath',
        'cursor',
        'defs',
        'ellipse',
        'foreignObject',
        'g',
        'image',
        'line',
        'mask',
        'path',
        'pattern',
        'polygon',
        'polyline',
        'rect',
        'set',
        'svg',
        'switch',
        'text',
        'textPath',
        'tref',
        'tspan',
        'use',
    ],
    'tableValues' => [
        'feFuncA',
        'feFuncB',
        'feFuncG',
        'feFuncR',
    ],
    'targetX' => [
        'feConvolveMatrix',
    ],
    'targetY' => [
        'feConvolveMatrix',
    ],
    'textLength' => [
        'text',
        'textPath',
        'tref',
        'tspan',
    ],
    'viewBox' => [
        'marker',
        'pattern',
        'svg',
        'symbol',
        'view',
    ],
    'xChannelSelector' => [
        'feDisplacementMap',
    ],
    'yChannelSelector' => [
        'feDisplacementMap',
    ],
];

$namespaceToTagNamesAndAttributes = [
    'Html' => [
        'tagNames' => $htmlTagNames,
        'attributeNameToTagNames' => [],
    ],
    'Svg' => [
        'tagNames' => $svgTagNames,
        'attributeNameToTagNames' => $svgAttributeNameToTagNames,
    ],
];

// Reserved words

$reservedWords = [
    '__halt_compiler',
    'abstract',
    'and',
    'array',
    'as',
    'break',
    'callable',
    'case',
    'catch',
    'class',
    'clone',
    'const',
    'continue',
    'declare',
    'default',
    'die',
    'do',
    'echo',
    'else',
    'elseif',
    'empty',
    'enddeclare',
    'endfor',
    'endforeach',
    'endif',
    'endswitch',
    'endwhile',
    'eval',
    'exit',
    'extends',
    'final',
    'finally',
    'fn',
    'for',
    'foreach',
    'function',
    'global',
    'goto',
    'if',
    'implements',
    'include',
    'include_once',
    'instanceof',
    'insteadof',
    'interface',
    'isset',
    'list',
    'match',
    'namespace',
    'new',
    'or',
    'print',
    'private',
    'protected',
    'public',
    'readonly',
    'require',
    'require_once',
    'return',
    'static',
    'switch',
    'throw',
    'trait',
    'try',
    'unset',
    'use',
    'var',
    'while',
    'xor',
    'yield',
    '__CLASS__',
    '__DIR__',
    '__FILE__',
    '__FUNCTION__',
    '__LINE__',
    '__METHOD__',
    '__NAMESPACE__',
    '__TRAIT__',
    'int',
    'float',
    'bool',
    'string',
    'true',
    'false',
    'null',
    'void',
    'iterable',
    'object',
    'mixed',
    'never',
];

// Delete old classes

// phpcs:ignore Squiz.Functions.GlobalFunction.Found
function delete_old_classes(string $path): void
{
    $dirs = glob($path . '/*', GLOB_ONLYDIR);
    assert(is_array($dirs));

    foreach ($dirs as $dir) {
        rmrf($dir);
    }
}

// phpcs:ignore Squiz.Functions.GlobalFunction.Found
function rmrf(string $path): void
{
    $files = glob($path . '/*');
    assert(is_array($files));

    foreach ($files as $file) {
        if (is_dir($file) && !is_link($file)) {
            rmrf($file);
        } else {
            assert(unlink($file) === true);
        }
    }

        assert(rmdir($path) === true);
}

delete_old_classes($basePath);

// Generate element classes

foreach ($namespaceToTagNamesAndAttributes as $namespace => $tagNamesAndAttributes) {
    $tagNames = $tagNamesAndAttributes['tagNames'];
    $attributeNameToTagNames = $tagNamesAndAttributes['attributeNameToTagNames'];

    foreach ($tagNames as $tagName) {
        // Gather the attributes that need special handling

        $tagAttributeNames = [];

        foreach ($attributeNameToTagNames as $attributeName => $attributeTagNames) {
            if (!in_array($tagName, $attributeTagNames)) {
                continue;
            }

            $tagAttributeNames[] = $attributeName;
        }

        array_unshift($tagAttributeNames, 'class');
        $tagAttributeNames = array_unique($tagAttributeNames);

        // Derive the class name

        $class = preg_replace_callback(
            '/-([a-z])/',
            fn ($match) => strtoupper($match[1]),
            $tagName,
        );
        assert(is_string($class));

        if (in_array($class, $reservedWords)) {
            $class .= '_';
        }

        // Generate the source code

        $src = "<?php\n";
        $src .= "\n";
        $src .= "declare(strict_types=1);\n";
        $src .= "\n";
        $src .= "namespace StefanFisk\\Vy\\Elements\\$namespace;\n";
        $src .= "\n";
        $src .= "use StefanFisk\\Vy\\Element;\n";
        $src .= "use StefanFisk\\Vy\\Elements\\Utils;\n";
        $src .= "\n";
        $src .= "use function array_filter;\n";
        $src .= "\n";
        $src .= "class $class\n";
        $src .= "{\n";
        $src .= "    public static function el(\n";
        foreach ($tagAttributeNames as $tagAttributeName) {
            $argName = Utils::attToArg($tagAttributeName);

            $src .= "        mixed \$$argName = null,\n";
        }
        $src .= "        string | null \$_key = null,\n";
        $src .= "        mixed ...\$props,\n";
        $src .= "    ): Element {\n";
        $src .= "        return new Element(\n";
        $src .= "            key: \$_key,\n";
        $src .= "            type: '$tagName',\n";
        $src .= "            props: array_filter(\n";
        $src .= "                [\n";
        foreach ($tagAttributeNames as $tagAttributeName) {
            $argName = Utils::attToArg($tagAttributeName);

            $src .= "                    '$tagAttributeName' => \$$argName,\n";
        }
        $src .= "                    ...Utils::mapArgsToAtts(\$props),\n";
        $src .= "                ],\n";
        $src .= "                fn (\$value) => \$value !== null,\n";
        $src .= "            ),\n";
        $src .= "        );\n";
        $src .= "    }\n";
        $src .= "}\n";

        if (!file_exists("$basePath/$namespace")) {
            mkdir("$basePath/$namespace", 0777, true);
        }

        file_put_contents("$basePath/$namespace/$class.php", $src);
    }
}
