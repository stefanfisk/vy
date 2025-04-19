#!/bin/env php
<?php // phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace StefanFisk\Vy;

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
use function strrchr;
use function strtoupper;
use function substr;
use function unlink;

use const GLOB_ONLYDIR;

require __DIR__ . '/../vendor/autoload.php';

$basePath = __DIR__ . '/../src/Elements';

// Tag defs

/** @var array<string,class-string<BaseElement>> $htmlTagNames */
$htmlTagNames = [
    'a' => Element::class,
    'abbr' => Element::class,
    'address' => Element::class,
    'area' => VoidElement::class,
    'article' => Element::class,
    'aside' => Element::class,
    'audio' => Element::class,
    'b' => Element::class,
    'base' => VoidElement::class,
    'bdi' => Element::class,
    'bdo' => Element::class,
    'blockquote' => Element::class,
    'body' => Element::class,
    'br' => VoidElement::class,
    'button' => Element::class,
    'canvas' => Element::class,
    'caption' => Element::class,
    'cite' => Element::class,
    'code' => Element::class,
    'col' => VoidElement::class,
    'colgroup' => Element::class,
    'data' => Element::class,
    'datalist' => Element::class,
    'dd' => Element::class,
    'del' => Element::class,
    'details' => Element::class,
    'dfn' => Element::class,
    'dialog' => Element::class,
    'div' => Element::class,
    'dl' => Element::class,
    'dt' => Element::class,
    'em' => Element::class,
    'embed' => VoidElement::class,
    'fieldset' => Element::class,
    'figcaption' => Element::class,
    'figure' => Element::class,
    'footer' => Element::class,
    'form' => Element::class,
    'h1' => Element::class,
    'h2' => Element::class,
    'h3' => Element::class,
    'h4' => Element::class,
    'h5' => Element::class,
    'h6' => Element::class,
    'head' => Element::class,
    'header' => Element::class,
    'hgroup' => Element::class,
    'hr' => VoidElement::class,
    'html' => Element::class,
    'i' => Element::class,
    'iframe' => Element::class,
    'img' => VoidElement::class,
    'input' => VoidElement::class,
    'ins' => Element::class,
    'kbd' => Element::class,
    'label' => Element::class,
    'legend' => Element::class,
    'li' => Element::class,
    'link' => VoidElement::class,
    'main' => Element::class,
    'map' => Element::class,
    'mark' => Element::class,
    'menu' => Element::class,
    'meta' => VoidElement::class,
    'meter' => Element::class,
    'nav' => Element::class,
    'noscript' => Element::class,
    'object' => Element::class,
    'ol' => Element::class,
    'optgroup' => Element::class,
    'option' => Element::class,
    'output' => Element::class,
    'p' => Element::class,
    'picture' => Element::class,
    'pre' => Element::class,
    'progress' => Element::class,
    'q' => Element::class,
    'rp' => Element::class,
    'rt' => Element::class,
    'ruby' => Element::class,
    's' => Element::class,
    'samp' => Element::class,
    'script' => Element::class,
    'search' => Element::class,
    'section' => Element::class,
    'select' => Element::class,
    'slot' => Element::class,
    'small' => Element::class,
    'source' => VoidElement::class,
    'span' => Element::class,
    'strong' => Element::class,
    'style' => Element::class,
    'sub' => Element::class,
    'summary' => Element::class,
    'sup' => Element::class,
    'table' => Element::class,
    'tbody' => Element::class,
    'td' => Element::class,
    'template' => Element::class,
    'textarea' => Element::class,
    'tfoot' => Element::class,
    'th' => Element::class,
    'thead' => Element::class,
    'time' => Element::class,
    'title' => Element::class,
    'tr' => Element::class,
    'track' => VoidElement::class,
    'u' => Element::class,
    'ul' => Element::class,
    'var' => Element::class,
    'video' => Element::class,
    'wbr' => VoidElement::class,
];

/** @var array<string,class-string<BaseElement>> $svgTagNames */
$svgTagNames = [
    'a' => Element::class,
    'animate' => Element::class,
    'animateMotion' => Element::class,
    'animateTransform' => Element::class,
    'circle' => VoidElement::class,
    'clipPath' => Element::class,
    'defs' => Element::class,
    'desc' => Element::class,
    'ellipse' => VoidElement::class,
    'feBlend' => Element::class,
    'feColorMatrix' => Element::class,
    'feComponentTransfer' => Element::class,
    'feComposite' => Element::class,
    'feConvolveMatrix' => Element::class,
    'feDiffuseLighting' => Element::class,
    'feDisplacementMap' => Element::class,
    'feDistantLight' => Element::class,
    'feDropShadow' => Element::class,
    'feFlood' => Element::class,
    'feFuncA' => Element::class,
    'feFuncB' => Element::class,
    'feFuncG' => Element::class,
    'feFuncR' => Element::class,
    'feGaussianBlur' => Element::class,
    'feImage' => Element::class,
    'feMerge' => Element::class,
    'feMergeNode' => Element::class,
    'feMorphology' => Element::class,
    'feOffset' => Element::class,
    'fePointLight' => Element::class,
    'feSpecularLighting' => Element::class,
    'feSpotLight' => Element::class,
    'feTile' => Element::class,
    'feTurbulence' => Element::class,
    'filter' => Element::class,
    'foreignObject' => Element::class,
    'g' => Element::class,
    'image' => VoidElement::class,
    'line' => VoidElement::class,
    'linearGradient' => Element::class,
    'marker' => Element::class,
    'mask' => Element::class,
    'metadata' => Element::class,
    'mpath' => Element::class,
    'path' => VoidElement::class,
    'pattern' => Element::class,
    'polygon' => VoidElement::class,
    'polyline' => VoidElement::class,
    'radialGradient' => Element::class,
    'rect' => VoidElement::class,
    'script' => Element::class,
    'set' => Element::class,
    'stop' => VoidElement::class,
    'style' => Element::class,
    'svg' => Element::class,
    'switch' => Element::class,
    'symbol' => Element::class,
    'text' => Element::class,
    'textPath' => Element::class,
    'title' => Element::class,
    'tspan' => Element::class,
    'use' => VoidElement::class,
    'view' => Element::class,
];

$namespaceToTagNames = [
    'Html' => $htmlTagNames,
    'Svg' => $svgTagNames,
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

foreach ($namespaceToTagNames as $namespace => $tagNames) {
    foreach ($tagNames as $tagName => $fqElementClass) {
        $elementClass = substr(strrchr($fqElementClass, '\\') ?: $fqElementClass, 1);

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
        $src .= "use $fqElementClass;\n";
        $src .= "\n";
        $src .= "final class $class\n";
        $src .= "{\n";
        $src .= "    /**\n";
        $src .= "     * @param ?non-empty-string \$_key\n";
        $src .= "     */\n";
        $src .= "    public static function el(\n";
        $src .= "        mixed \$class = null,\n";
        $src .= "        ?string \$_key = null,\n";
        $src .= "        mixed ...\$props,\n";
        $src .= "    ): $elementClass {\n";
        $src .= "        if (\$class !== null) {\n";
        $src .= "            \$props['class'] = \$class;\n";
        $src .= "        }\n";
        $src .= "\n";
        $src .= "        return new $elementClass(\n";
        $src .= "            key: \$_key,\n";
        $src .= "            type: '$tagName',\n";
        $src .= "            props: \$props,\n";
        $src .= "        );\n";
        $src .= "    }\n";
        $src .= "}\n";

        // Write the file

        if (!file_exists("$basePath/$namespace")) {
            mkdir("$basePath/$namespace", 0777, true);
        }

        file_put_contents("$basePath/$namespace/$class.php", $src);
    }
}
