<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends Exception implements ContainerExceptionInterface
{
}
