<?php

declare(strict_types=1);

namespace StefanFisk\PhpReact\Hooks;

use Closure;
use StefanFisk\PhpReact\Components\Context;
use StefanFisk\PhpReact\Errors\HookException;
use StefanFisk\PhpReact\Node;
use StefanFisk\PhpReact\Renderer;

use function assert;
use function is_string;
use function is_subclass_of;

class ContextHook extends Hook
{
    /**
     * @param class-string<Context> $context
     *
     * @return array{mixed,Closure(mixed):void}
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

        $contextNode = $this->getContextNode($node->parent);

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

    private function getContextNode(Node | null $node): Node | null
    {
        for (; $node; $node = $node->parent) {
            if (
                $node->type instanceof Context
                || is_string($node->type) && is_subclass_of($node->type, Context::class)
            ) {
                return $node;
            }
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
