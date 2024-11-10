<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Hooks;

use Closure;
use StefanFisk\Vy\Components\Context;
use StefanFisk\Vy\Errors\HookException;
use StefanFisk\Vy\Rendering\Node;
use StefanFisk\Vy\Rendering\Renderer;

use function assert;

class ContextHook extends Hook
{
    /**
     * @param class-string<Context> $context
     */
    public static function use(string $context): mixed
    {
        return static::useWith($context);
    }

    private mixed $value;
    private mixed $nextValue;
    private readonly Closure $unsubscribe;

    /** @param class-string<Context> $context */
    public function __construct(
        Renderer $renderer,
        Node $node,
        private readonly string $context,
    ) {
        parent::__construct(
            renderer: $renderer,
            node: $node,
        );

        $contextNode = $this->getContextNode($context, $node->parent);

        if (!$contextNode) {
            $this->nextValue = $context::getDefaultValue();
            $this->value = $this->nextValue;
            $this->unsubscribe = function (): void {
            };
        } else {
            $this->nextValue = $contextNode->props['value'] ?? null;
            $this->value = $this->nextValue;

            $providerHook = $contextNode->hooks[0];
            assert($providerHook instanceof ContextProviderHook);

            $this->unsubscribe = $providerHook->subscribe($this->valueDidChange(...));
        }
    }

    public function needsRender(): bool
    {
        return $this->value !== $this->nextValue;
    }

    public function initialRender(mixed ...$args): mixed
    {
        return $this->value;
    }

    public function rerender(mixed ...$args): mixed
    {
        /** @var string $context */
        $context = $args[0] ?? null;

        if ($context !== $this->context) {
            throw new HookException(
                message: 'ContextHook::use() must be called with the same context on every render.',
                hook: self::class,
                node: $this->node,
            );
        }

        $this->value = $this->nextValue;

        return $this->value;
    }

    public function unmount(): void
    {
        ($this->unsubscribe)();
    }

    /**
     * @param class-string<Context> $context
     */
    private function getContextNode(string $context, ?Node $node): ?Node
    {
        for (; $node; $node = $node->parent) {
            $hook = $node->hooks[0] ?? null;

            if (!$hook instanceof ContextProviderHook) {
                continue;
            }

            if ($hook->context !== $context) {
                continue;
            }

            return $node;
        }

        return null;
    }

    private function valueDidChange(mixed $newValue): void
    {
        $this->nextValue = $newValue;

        if ($newValue === $this->value) {
            return;
        }

        $this->renderer->enqueueRender($this->node);
    }
}
