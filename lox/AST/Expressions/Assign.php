<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scan\Token;

class Assign extends Expression
{

    public function __construct(
        public readonly Token      $name,
        public readonly Expression $value
    )
    {
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitAssignExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        // TODO: Implement jsonSerialize() method.
    }
}