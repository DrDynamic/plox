<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;

class Literal extends Expression
{
    public function __construct(
        public readonly mixed $value
    )
    {
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitLiteral($this);
    }
}