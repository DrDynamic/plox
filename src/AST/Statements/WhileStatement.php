<?php

namespace src\AST\Statements;

use src\AST\Expressions\Expression;
use src\AST\StatementVisitor;
use src\Scaner\Token;

class WhileStatement extends Statement
{

    public function __construct(
        public readonly Token      $token,
        public readonly Expression $condition,
        public readonly Statement  $body
    )
    {
        parent::__construct($this->token, $this->body->tokenEnd);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        return $visitor->visitWhileStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'condition' => $this->condition,
            'body'      => $this->body
        ];
    }
}