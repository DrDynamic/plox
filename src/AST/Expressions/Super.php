<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;

class Super extends Expression
{

    public function __construct(public readonly Token $keyword,
                                public readonly Token $method)
    {
        parent::__construct($keyword, $method);
    }

    function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitSuperExpression($this);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'keyword' => $this->keyword,
            'method'  => $this->method,
        ];
    }
}