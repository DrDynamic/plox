<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;

class Grouping extends Expression
{
    public function __construct(
        public readonly Expression $expression
    )
    {
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitGroupingExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'group' => $this->expression
        ];
    }
}