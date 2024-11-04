<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;

class ThisExpression extends Expression
{

    public function __construct(public readonly Token $keyword)
    {
        parent::__construct($keyword, $keyword);
    }

    function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitThisExpression($this);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'keyword' => $this->keyword
        ];
    }
}