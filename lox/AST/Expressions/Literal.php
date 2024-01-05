<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Runtime\Values\BaseValue;
use Lox\Scan\Token;

class Literal extends Expression
{
    public function __construct(
        public readonly BaseValue $value,
        public readonly Token     $token
    )
    {
        parent::__construct($this->token, $this->token);
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