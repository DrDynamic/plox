<?php

namespace Lox\AST\Statements;

use Lox\AST\StatementVisitor;
use Lox\Scan\Token;

class CompletionStatement extends Statement
{
    public function __construct(
        public readonly Token $operator
    )
    {
    }


    #[\Override] function accept(StatementVisitor $visitor)
    {
        $visitor->visitCompletionStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'operator' => $this->operator
        ];
    }
}