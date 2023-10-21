<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function array_filter;
use function array_map;
use function array_values;
use function assert;
use function count;

final class ElementChildren extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        assert($node instanceof FuncCall);

        if ($this->getName($node) !== 'StefanFisk\\Vy\\el') {
            return null;
        }

        if (count($node->args) < 3) {
            return null;
        }

        $childArgs = [];
        for ($i = 2, $c = count($node->args); $i < $c; $i++) {
            $childArgs[] = $node->args[$i];
            unset($node->args[$i]);
        }

        if ($node->args[1] instanceof Arg && $node->args[1]->value instanceof Array_) {
            if (count($node->args[1]->value->items) === 0) {
                unset($node->args[1]);
            }
        }

        if (count($childArgs) === 1 && $childArgs[0] instanceof Arg) {
            $childArg = $childArgs[0];

            if ($childArg->value instanceof Array_) {
                $childArgs = array_values(array_filter(array_map(
                    fn (ArrayItem | null $item) => $item ? new Arg(
                        value: $item->value,
                    ) : null,
                    $childArg->value->items,
                )));
            }
        }

        return new FuncCall(
            name: $node,
            args: $childArgs,
        );
    }

    /**
     * This method helps other to understand the rule and to generate documentation.
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change StefanFisk\Vy\el() child API.',
            [
                new CodeSample(
                    "el('div', [], 'foo')",
                    "el('div')('foo')",
                ),
            ],
        );
    }
}
