<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;
use Lox\Scaner\Token;

class VarStatement extends Statement
{

    public function __construct(
        public readonly Token           $startToken,
        public readonly Token           $name,
        public readonly Expression|null $initializer
    )
    {
        $end = $this->initializer != null ? $this->initializer->tokenEnd : $this->name;
        parent::__construct($this->startToken, $end);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        $visitor->visitVarStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'name'        => $this->name,
            'initializer' => $this->initializer
        ];
    }
}