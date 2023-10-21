<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use StefanFisk\Vy\Rector\ElementChildren;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/examples',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->rule(ElementChildren::class);
};
