<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

interface HookHandlerInterface
{
    /** @param class-string<Hook> $class */
    public function useHook(string $class, mixed ...$args): mixed;
}
