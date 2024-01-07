<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;

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
        $visitor->visitExpressionStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'expression' => $this->expression
        ];
    }
}