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
    private const NODE_TYPE_COMPONENT_CLOSURE = 'COMPONENT_CLOSURE';
    private const NODE_TYPE_COMPONENT_OBJECT = 'COMPONENT_OBJECT';
    private const NODE_TYPE_COMPONENT_CLASS = 'COMPONENT_CLASS';
    private const NODE_TYPE_TAG = 'TAG';

    /** @var array<string,(self::NODE_TYPE_COMPONENT_CLOSURE|self::NODE_TYPE_COMPONENT_OBJECT|self::NODE_TYPE_COMPONENT_CLASS|self::NODE_TYPE_TAG|string)> */
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

        if (!$nodeType) {
            if (is_string($type)) {
                if (str_contains($type, '\\') && class_exists($type)) {
                    if ($this->classIsRenderable($type)) {
                        $nodeType = self::NODE_TYPE_COMPONENT_CLASS;
                    } else {
                        $nodeType = sprintf(
                            '`type` class `%s` does not have a public `render()` method.',
                            $type,
                        );
                    }
                } else {
                    $nodeType = self::NODE_TYPE_TAG;
                }
            } elseif ($type instanceof Closure) {
                $nodeType = self::NODE_TYPE_COMPONENT_CLOSURE;
            } elseif (is_object($type)) {
                if (! $this->classIsRenderable($type::class)) {
                    $nodeType = sprintf(
                        '`type` object of class `%s` does not have a public `render()` method.',
                        $type::class,
                    );
                }

                $nodeType = self::NODE_TYPE_COMPONENT_OBJECT;
            } else {
                $nodeType = sprintf('Unsupported type `%s`.', gettype($type));
            }
        }

        if (is_string($type)) {
            $this->elementTypeToNodeType[$type] = $nodeType;
        }

        // Create node

        if (
            $nodeType === self::NODE_TYPE_COMPONENT_CLOSURE
            || $nodeType === self::NODE_TYPE_COMPONENT_OBJECT
            || $nodeType === self::NODE_TYPE_COMPONENT_CLASS
        ) {
            if ($nodeType === self::NODE_TYPE_COMPONENT_CLOSURE) {
                /** @var Closure $component */
                $component = $type;
            } elseif ($nodeType === self::NODE_TYPE_COMPONENT_OBJECT) {
                /**
                 * @var Closure $component
                 * @phpstan-ignore-next-line
                 * @psalm-suppress all
                 */
                $component = $type->render(...);
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
        } elseif ($nodeType === self::NODE_TYPE_TAG) {
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

    /** @param class-string $class */
    private function classIsRenderable(string $class): bool
    {
        $class = new ReflectionClass($class);
        if (!$class->hasMethod('render')) {
            return false;
        }

        $method = $class->getMethod('render');

        return $method->isPublic();
    }
}
