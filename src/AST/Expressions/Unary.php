<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;

class Unary extends Expression
{
    public function __construct(
        public readonly Token      $operator,
        public readonly Expression $right
    )
    {
        parent::__construct($this->operator, $this->right->tokenEnd);
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitUnaryExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [$this->operator->lexeme, $this->right];
    }
}