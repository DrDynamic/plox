<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;

class Set extends Expression
{
    public function __construct(
        public readonly Expression $object,
        public readonly Token      $name,
        public readonly Expression $value)
    {
        parent::__construct($object->tokenStart, $this->value->tokenEnd);
    }

    function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitSetExpression($this);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'object' => $this->object,
            'name'   => $this->name,
            'value'  => $this->value
        ];
    }
}