<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Elements;

use function array_combine;
use function array_keys;
use function array_map;
use function assert;
use function is_int;
use function preg_replace;
use function strtolower;

class Utils
{
    /**
     * @param array<int|string,mixed> $arr
     *
     * @return array<int|string,mixed>
     */
    public static function mapKeysToKebab(array $arr): array
    {
        $mappedKeys = array_map(fn ($k) => is_int($k) ? $k : self::camelToKebab($k), array_keys($arr));

        return array_combine($mappedKeys, $arr);
    }

    private static function camelToKebab(string $input): string
    {
        $str = preg_replace('/[A-Z]/', '-$0', $input);

        assert($str !== null);

        return strtolower($str);
    }
}
