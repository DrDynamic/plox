<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scan\Token;

class Unary extends Expression
{
    public function __construct(
        public readonly Token      $operator,
        public readonly Expression $right
    )
    {
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