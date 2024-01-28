<?php

namespace src\AST\Statements;

use src\AST\StatementVisitor;
use src\Scaner\Token;

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
        return $visitor->visitCompletionStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'operator' => $this->operator
        ];
    }
}