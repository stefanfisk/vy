<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use StefanFisk\Vy\Rector\ElementChildren;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ElementChildren::class);
};
