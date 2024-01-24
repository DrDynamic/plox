<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;
use Lox\Scaner\Token;

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