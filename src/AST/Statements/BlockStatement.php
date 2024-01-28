<?php

namespace src\AST\Statements;

use src\AST\StatementVisitor;
use src\Scaner\Token;

class BlockStatement extends Statement
{
    /**
     * @param array<Statement> $statements
     */
    public function __construct(
        public readonly Token $leftBrace,
        public readonly array $statements,
        public readonly Token $rightBrace)
    {
        parent::__construct($this->leftBrace, $this->rightBrace);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        return $visitor->visitBlockStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return $this->statements;
    }
}