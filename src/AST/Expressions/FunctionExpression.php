<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;
use src\Services\Arr;

class FunctionExpression extends Expression
{
    public function __construct(Token                      $tokenStart,
                                public readonly Token|null $name,
                                public readonly array      $parameters,
                                public array               $body)
    {
        parent::__construct($tokenStart, Arr::last($this->body)->tokenEnd);
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitFunctionExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'name'       => $this->name,
            'parameters' => $this->parameters,
            'body'       => $this->body,
        ];
    }
}