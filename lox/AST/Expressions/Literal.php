<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Runtime\Values\Value;

class Literal extends Expression
{
    public function __construct(
        public readonly Value $value
    )
    {
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitLiteralExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}