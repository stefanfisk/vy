<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html;

use Override;

final class CachingPropToAttrNameMapper implements PropToAttrNameMapper
{
    public const DEFAULT_PROP_TO_ATTR_NAME = [
        // SVG attributes that cannot be converted to kebab case as listed at https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute
        'attributeName' => 'attributeName',
        'attributeType' => 'attributeType',
        'baseFrequency' => 'baseFrequency',
        'baseProfile' => 'baseProfile',
        'calcMode' => 'calcMode',
        'clipPathUnits' => 'clipPathUnits',
        'contentScriptType' => 'contentScriptType',
        'contentStyleType' => 'contentStyleType',
        'diffuseConstant' => 'diffuseConstant',
        'edgeMode' => 'edgeMode',
        'filterRes' => 'filterRes',
        'filterUnits' => 'filterUnits',
        'glyphRef' => 'glyphRef',
        'gradientTransform' => 'gradientTransform',
        'gradientUnits' => 'gradientUnits',
        'kernelMatrix' => 'kernelMatrix',
        'kernelUnitLength' => 'kernelUnitLength',
        'keyPoints' => 'keyPoints',
        'keySplines' => 'keySplines',
        'keyTimes' => 'keyTimes',
        'lengthAdjust' => 'lengthAdjust',
        'limitingConeAngle' => 'limitingConeAngle',
        'markerHeight' => 'markerHeight',
        'markerUnits' => 'markerUnits',
        'markerWidth' => 'markerWidth',
        'maskContentUnits' => 'maskContentUnits',
        'maskUnits' => 'maskUnits',
        'numOctaves' => 'numOctaves',
        'pathLength' => 'pathLength',
        'patternContentUnits' => 'patternContentUnits',
        'patternTransform' => 'patternTransform',
        'patternUnits' => 'patternUnits',
        'pointsAtX' => 'pointsAtX',
        'pointsAtY' => 'pointsAtY',
        'pointsAtZ' => 'pointsAtZ',
        'preserveAlpha' => 'preserveAlpha',
        'preserveAspectRatio' => 'preserveAspectRatio',
        'primitiveUnits' => 'primitiveUnits',
        'referrerPolicy' => 'referrerPolicy',
        'refX' => 'refX',
        'refY' => 'refY',
        'repeatCount' => 'repeatCount',
        'repeatDur' => 'repeatDur',
        'requiredExtensions' => 'requiredExtensions',
        'requiredFeatures' => 'requiredFeatures',
        'specularConstant' => 'specularConstant',
        'specularExponent' => 'specularExponent',
        'spreadMethod' => 'spreadMethod',
        'startOffset' => 'startOffset',
        'stdDeviation' => 'stdDeviation',
        'stitchTiles' => 'stitchTiles',
        'surfaceScale' => 'surfaceScale',
        'systemLanguage' => 'systemLanguage',
        'tableValues' => 'tableValues',
        'targetX' => 'targetX',
        'targetY' => 'targetY',
        'textLength' => 'textLength',
        'viewBox' => 'viewBox',
        'viewTarget' => 'viewTarget',
        'xChannelSelector' => 'xChannelSelector',
        'xlinkActuate' => 'xlink:actuate',
        'xlinkArcrole' => 'xlink:arcrole',
        'xlinkHref' => 'xlink:href',
        'xlinkRole' => 'xlink:role',
        'xlinkShow' => 'xlink:show',
        'xlinkTitle' => 'xlink:title',
        'xlinkType' => 'xlink:type',
        'xmlBase' => 'xml:base',
        'xmlLang' => 'xml:lang',
        'xmlSpace' => 'xml:space',
        'yChannelSelector' => 'yChannelSelector',
        'zoomAndPan' => 'zoomAndPan',
    ];

    /** @var array<non-empty-string,?non-empty-string> */
    private array $propToAttrName;

    /**
     * @param array<PropToAttrNameMapper> $mappers
     * @param array<non-empty-string,non-empty-string> $propToAttrName
     */
    public function __construct(
        private readonly array $mappers,
        array $propToAttrName = self::DEFAULT_PROP_TO_ATTR_NAME,
    ) {
        $this->propToAttrName = $propToAttrName;
    }

    #[Override]
    public function propToAttrName(string $propName): ?string
    {
        if (isset($this->propToAttrName[$propName])) {
            return $this->propToAttrName[$propName];
        }

        $attrName = null;

        foreach ($this->mappers as $mapper) {
            $attrName = $mapper->propToAttrName($propName);

            if ($attrName === null) {
                continue;
            }

            break;
        }

        $this->propToAttrName[$propName] = $attrName;

        return $attrName;
    }
}
