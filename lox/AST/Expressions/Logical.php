<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scan\Token;

class Logical extends Expression
{

    public function __construct(
        public readonly Expression $left,
        public readonly Token      $operator,
        public readonly Expression $right
    )
    {
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitLogical($this);
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