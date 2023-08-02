<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Errors;

use Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
