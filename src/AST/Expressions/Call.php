<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;

class Call extends Expression
{

    /**
     * @param Expression $callee
     * @param Token $paren
     * @param array<Expression> $arguments
     */
    public function __construct(
        public readonly Expression $callee,
        public readonly array      $arguments,
        public readonly Token      $rightParen
    )
    {
        parent::__construct($this->callee->tokenStart, $this->rightParen);
    }

    #[\Override] function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitCallExpr($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'callee'    => $this->callee,
            'arguments' => $this->arguments,
        ];
    }
}