<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Serialization\Html;

use Closure;
use Override;
use Stringable;
use UnexpectedValueException;

use function get_debug_type;
use function is_float;
use function is_int;
use function is_string;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;
use function sprintf;

final class UnsafeHtml implements HtmlableInterface
{
    public static function from(string | Closure $html): static
    {
        return new static($html);
    }

    public function __construct(private readonly string | Closure $html)
    {
    }

    #[Override]
    public function toHtml(): string
    {
        if (is_string($this->html)) {
            return $this->html;
        }

        $obLevel = ob_get_level();

        try {
            ob_start();
            $ret = ($this->html)();
            $output = (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
        }

        if ($ret !== null) {
            if (!is_int($ret) && !is_float($ret) && !is_string($ret) && !$ret instanceof Stringable) {
                throw new UnexpectedValueException(sprintf(
                    'Closure returned %s, must be null, int, float, string or Stringable.',
                    get_debug_type($ret),
                ));
            }

            return (string) $ret;
        } else {
            return $output;
        }
    }
}
