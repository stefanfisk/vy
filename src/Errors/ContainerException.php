<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Errors;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends Exception implements ContainerExceptionInterface
{
}
