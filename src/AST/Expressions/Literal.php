<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Interpreter\Runtime\Values\Value;
use src\Scaner\Token;

class Literal extends Expression
{
    public function __construct(
        public readonly Value $value,
        public readonly Token $token
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