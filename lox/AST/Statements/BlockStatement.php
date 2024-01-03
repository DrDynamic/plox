<?php

namespace Lox\AST\Statements;

use Lox\AST\StatementVisitor;

class BlockStatement extends Statement
{
    /**
     * @param array<Statement> $statements
     */
    public function __construct(
        public readonly array $statements)
    {
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        $visitor->visitBlockStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return $this->statements;
    }
}