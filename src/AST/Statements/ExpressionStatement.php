<?php

namespace src\AST\Statements;

use src\AST\Expressions\Expression;
use src\AST\StatementVisitor;

class ExpressionStatement extends Statement
{
    public function __construct(
        public readonly Expression $expression,
    )
    {
        parent::__construct($this->expression->tokenStart, $this->expression->tokenEnd);
    }


    #[\Override] function accept(StatementVisitor $visitor)
    {
        return $visitor->visitExpressionStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'expression' => $this->expression
        ];
    }
}