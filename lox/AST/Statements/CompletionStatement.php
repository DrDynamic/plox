<?php

namespace Lox\AST\Statements;

use Lox\AST\StatementVisitor;
use Lox\Scaner\Token;

class CompletionStatement extends Statement
{
    public function __construct(
        public readonly Token $operator,
    )
    {
        parent::__construct($this->operator, $this->operator);
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