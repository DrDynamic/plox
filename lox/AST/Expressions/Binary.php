<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scan\Token;

class Binary extends Expression
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
        return $visitor->visitBinary($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'left' => $this->left,
            'operator' => $this->operator->lexeme,
            'right' => $this->right
        ];
    }
}