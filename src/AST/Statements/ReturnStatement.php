<?php

namespace src\AST\Statements;

use src\AST\Expressions\Expression;
use src\AST\StatementVisitor;
use src\Scaner\Token;

class ReturnStatement extends Statement
{
    public function __construct(
        public readonly Token      $keyword,
        public readonly Expression $value)
    {
        parent::__construct($keyword, $value->tokenEnd);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        $visitor->visitReturnStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'keyword' => $this->keyword,
            'value'   => $this->value,
        ];
    }
}