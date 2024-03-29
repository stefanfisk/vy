<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Errors;

use Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
