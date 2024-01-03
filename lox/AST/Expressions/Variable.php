<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scan\Token;

class Variable extends Expression
{

    public function __construct(
        public readonly Token $name
    )
    {
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitVariableExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name
        ];
    }
}