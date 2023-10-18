<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements;

use function array_combine;
use function array_keys;
use function array_map;
use function assert;
use function explode;
use function implode;
use function is_int;
use function lcfirst;
use function preg_replace;
use function str_replace;
use function strtolower;
use function ucfirst;

class Utils
{
    /** @var array<string,string> */
    private static array $argToAttCache = [];

    public static function clearCache(): void
    {
        self::$argToAttCache = [];
    }

    public static function attToArg(string $att): string
    {
        $words = explode(' ', str_replace(['-', '_', ':'], ' ', $att));

        $studlyWords = array_map(fn ($word) => ucfirst($word), $words);

        return lcfirst(implode('', $studlyWords));
    }

    /**
     * @param array<int|string,mixed> $arr
     *
     * @return array<int|string,mixed>
     */
    public static function mapArgsToAtts(array $arr): array
    {
        $mappedKeys = array_map(
            fn ($key) => is_int($key) ? $key : self::argToAtt($key),
            array_keys($arr),
        );

        return array_combine($mappedKeys, $arr);
    }

    public static function argToAtt(string $arg): string
    {
        if (isset(self::$argToAttCache[$arg])) {
            return self::$argToAttCache[$arg];
        }

        $att = preg_replace('/[A-Z]/', '-$0', $arg);

        assert($att !== null);

        $att = strtolower($att);

        self::$argToAttCache[$arg] = $att;

        return $att;
    }
}
