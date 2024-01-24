<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scaner\Token;

class Grouping extends Expression
{
    public function __construct(
        public readonly Token      $leftParen,
        public readonly Expression $expression,
        public readonly Token      $rightParen
    )
    {
        parent::__construct($this->leftParen, $this->rightParen);
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