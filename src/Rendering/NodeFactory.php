<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rendering;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use StefanFisk\Vy\Container;
use StefanFisk\Vy\Element;
use StefanFisk\Vy\Errors\ContainerException;
use StefanFisk\Vy\Errors\InvalidElementTypeException;

use function class_exists;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
use function str_contains;

class NodeFactory
{
    private const NODE_CLOSURE = 1;
    private const NODE_OBJECT = 2;
    private const NODE_INSTANCE_RENDER = 3;
    private const NODE_STATIC_RENDER = 4;
    private const NODE_TAG = 5;

    /** @var array<string,(self::NODE_CLOSURE|self::NODE_OBJECT|self::NODE_INSTANCE_RENDER|self::NODE_STATIC_RENDER|self::NODE_TAG|non-empty-string)> */
    private array $elementTypeToNodeType = [];

    private int $nextNodeId = 0;

    public function __construct(
        private readonly ContainerInterface $container = new Container(),
    ) {
    }

    public function createNode(Element $el, Node | null $parent): Node
    {
        $key = $el->key;
        $type = $el->type;

        $nodeType = null;

        // Cached

        if (is_string($type)) {
            $nodeType = $this->elementTypeToNodeType[$type] ?? null;
        }

        // Resolve type

        if ($nodeType === null) {
            $nodeType = $this->resolveNodeType($type);
        }

        if (is_string($type)) {
            $this->elementTypeToNodeType[$type] = $nodeType;
        }

        // Create node

        if (
            $nodeType === self::NODE_CLOSURE
            || $nodeType === self::NODE_OBJECT
            || $nodeType === self::NODE_STATIC_RENDER
            || $nodeType === self::NODE_INSTANCE_RENDER
        ) {
            if ($nodeType === self::NODE_CLOSURE) {
                /** @var Closure $component */
                $component = $type;
            } elseif ($nodeType === self::NODE_OBJECT) {
                /**
                 * @var Closure $component
                 * @phpstan-ignore-next-line
                 * @psalm-suppress all
                 */
                $component = $type->render(...);
            } elseif ($nodeType === self::NODE_STATIC_RENDER) {
                /**
                 * @var Closure $component
                 * @phpstan-ignore-next-line
                 * @psalm-suppress all
                 */
                $component = $type::render(...);
            } else {
                /** @var class-string $type */
                $type = $type;

                $instance = $this->container->get($type);

                if (! $instance || ! is_object($instance) || ! $instance instanceof $type) {
                    throw new ContainerException(sprintf(
                        'Container did not return instance of class `%s`.',
                        $type,
                    ));
                }

                /**
                 * @phpstan-ignore-next-line
                 * @psalm-suppress MixedMethodCall
                 */
                $component = $instance->render(...);
            }

            return new Node(
                id: $this->nextNodeId++,
                parent: $parent,
                key: $key,
                type: $type,
                component: $component,
            );
        } elseif ($nodeType === self::NODE_TAG) {
            /** @var string $type */
            $type = $type;

            if ($type === '') {
                throw new InvalidElementTypeException(
                    message: '`type` cannot be empty string.',
                    el: $el,
                    parentNode: $parent,
                );
            }

            return new Node(
                id: $this->nextNodeId++,
                parent: $parent,
                key: $key,
                type: $type,
                component: null,
            );
        } else {
            throw new InvalidElementTypeException(
                message: $nodeType,
                el: $el,
                parentNode: $parent,
            );
        }
    }

    /** @return self::NODE_CLOSURE|self::NODE_OBJECT|self::NODE_INSTANCE_RENDER|self::NODE_STATIC_RENDER|self::NODE_TAG|non-empty-string */
    private function resolveNodeType(mixed $type): mixed
    {
        if (is_string($type)) {
            if (str_contains($type, '\\')) {
                if (!class_exists($type)) {
                    return sprintf(
                        '`type` class `%s` does not exist.',
                        $type,
                    );
                }

                $class = new ReflectionClass($type);

                if (!$class->hasMethod('render')) {
                    return sprintf(
                        '`type` class `%s` does not have a `render()` method.',
                        $type,
                    );
                }

                $method = $class->getMethod('render');

                if (!$method->isPublic()) {
                    return sprintf(
                        '`type` class `%s` does not have a public `render()` method.',
                        $type,
                    );
                }

                if ($method->isAbstract()) {
                    return sprintf(
                        '`type` class `%s` `render()` method is abstract.',
                        $type,
                    );
                }

                if ($method->isStatic()) {
                    return self::NODE_STATIC_RENDER;
                } else {
                    return self::NODE_INSTANCE_RENDER;
                }
            } else {
                return self::NODE_TAG;
            }
        }

        if ($type instanceof Closure) {
            return self::NODE_CLOSURE;
        }

        if (is_object($type)) {
            $class = new ReflectionClass($type);

            if (!$class->hasMethod('render')) {
                return sprintf(
                    '`type` object of class `%s` does not have a `render()` method.',
                    $type::class,
                );
            }

            $method = $class->getMethod('render');

            if (!$method->isPublic()) {
                return sprintf(
                    '`type` object of class `%s` does not have a public `render()` method.',
                    $type::class,
                );
            }

            if ($method->isAbstract()) {
                return sprintf(
                    '`type` object of class `%s` `render()` method is abstract.',
                    $type::class,
                );
            }

            if ($method->isStatic()) {
                return self::NODE_STATIC_RENDER;
            } else {
                return self::NODE_OBJECT;
            }
        }

        return sprintf('Unsupported type `%s`.', gettype($type));
    }
}
