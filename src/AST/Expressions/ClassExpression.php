<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;
use src\Services\Arr;

class ClassExpression extends Expression
{

    public function __construct(Token                         $tokenStart,
                                public readonly Token|null    $name,
                                public readonly Variable|null $superClass,
                                public readonly array         $body)
    {
        parent::__construct($tokenStart, Arr::last($body)->tokenEnd);
    }

    function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitClassExpression($this);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name'       => $this->name,
            'superClass' => $this->superClass,
            'body'       => $this->body
        ];
    }
}