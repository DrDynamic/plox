<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;
use Lox\Scan\Token;

class VarStatement extends Statement
{

    public function __construct(
        public readonly Token           $name,
        public readonly Expression|null $initializer
    )
    {
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