<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Runtime\Types\Type;

class Literal extends Expression
{
    public function __construct(
        public readonly Type $value
    )
    {
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitLiteral($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}