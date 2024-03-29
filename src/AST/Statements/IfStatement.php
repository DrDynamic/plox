<?php

namespace src\AST\Statements;

use src\AST\Expressions\Expression;
use src\AST\StatementVisitor;
use src\Scaner\Token;

class IfStatement extends Statement
{

    public function __construct(
        public readonly Token          $startToken,
        public readonly Expression     $condition,
        public readonly Statement      $thenBranch,
        public readonly Statement|null $elseBranch
    )
    {
        $end = $this->elseBranch != null ? $this->elseBranch->tokenEnd : $this->thenBranch->tokenEnd;
        parent::__construct($this->startToken, $end);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        return $visitor->visitIfStmt($this);
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