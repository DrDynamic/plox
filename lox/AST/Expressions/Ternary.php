<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scan\Token;

class Ternary extends Expression
{
    public function __construct(
        public readonly Expression $condition,
        public readonly Token      $question,
        public readonly Expression $then,
        public readonly Token      $colon,
        public readonly Expression $else
    )
    {
        parent::__construct($this->condition->tokenStart, $this->else->tokenEnd);
    }


    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitTernaryExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'condition' => $this->condition,
            'question'  => $this->question,
            'then'      => $this->then,
            'colon'     => $this->colon,
            'else'      => $this->else
        ];
    }
}