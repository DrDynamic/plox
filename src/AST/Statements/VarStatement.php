<?php

namespace src\AST\Statements;

use src\AST\Expressions\Expression;
use src\AST\StatementVisitor;
use src\Scaner\Token;

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
        return $visitor->visitVarStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'name'        => $this->name,
            'initializer' => $this->initializer
        ];
    }
}