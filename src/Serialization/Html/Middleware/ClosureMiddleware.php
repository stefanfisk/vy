<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Serialization\Html\Middleware;

use Closure;
use Throwable;
use UnexpectedValueException;

use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;

class ClosureMiddleware extends TransformingMiddleware
{
    public function transformValue(mixed $value): mixed
    {
        if (!$value instanceof Closure) {
            return $value;
        }

        $obLevel = ob_get_level();

        try {
            ob_start();

            $ret = $value();
            $output = (string) ob_get_clean();

            if ($ret !== null && $output !== '') {
                throw new UnexpectedValueException('Closure value cannot both return and output value.');
            }

            return $output ?: $ret;
        } catch (Throwable $e) {
            while ($obLevel < ob_get_level()) {
                ob_end_clean();
            }

            throw $e;
        }
    }
}
