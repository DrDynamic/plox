<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;

class IfStatement extends Statement
{

    public function __construct(
        public readonly Expression     $condition,
        public readonly Statement      $thenBranch,
        public readonly Statement|null $elseBranch
    )
    {
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        $visitor->visitIfStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'condition'  => $this->condition,
            'thenBranch' => $this->thenBranch,
            'elseBranch' => $this->elseBranch,
        ];
    }
}