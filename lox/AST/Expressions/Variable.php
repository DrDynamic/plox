<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scaner\Token;

class Variable extends Expression
{

    public function __construct(
        public readonly Token $name
    )
    {
        parent::__construct($this->name, $this->name);
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