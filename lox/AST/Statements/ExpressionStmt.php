<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;

class ExpressionStmt extends Statement
{
    public function __construct(
        public readonly Expression $expression
    )
    {
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