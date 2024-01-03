<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;

class WhileStatement extends Statement
{

    public function __construct(
        public readonly Expression $condition,
        public readonly Statement  $body
    )
    {
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        $visitor->visitWhileStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'condition' => $this->condition,
            'body'      => $this->body
        ];
    }
}