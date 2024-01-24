<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scaner\Token;

class Logical extends Expression
{

    public function __construct(
        public readonly Expression $left,
        public readonly Token      $operator,
        public readonly Expression $right
    )
    {
        parent::__construct($this->left->tokenStart, $this->right->tokenEnd);
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitLogicalExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'left'     => $this->left,
            'operator' => $this->operator,
            'right'    => $this->right,
        ];
    }
}