<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;

class Get extends Expression
{
    public function __construct(
        public readonly Expression $object,
        public readonly Token      $name)
    {
        parent::__construct($object->tokenStart, $name);
    }

    function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitGetExpression($this);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'object' => $this->object,
            'name'   => $this->name
        ];
    }
}