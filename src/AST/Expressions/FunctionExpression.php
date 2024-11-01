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
        $tokenEnd = $tokenStart;
        if ($name != null) {
            $tokenEnd = $name;
        }
        if (count($this->body) > 0) {
            $tokenEnd = Arr::last($body)->tokenEnd;
        }
        parent::__construct($tokenStart, $tokenEnd);
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