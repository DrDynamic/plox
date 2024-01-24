<?php

namespace Lox\AST\Statements;

use Lox\AST\Expressions\Expression;
use Lox\AST\StatementVisitor;
use Lox\Scaner\Token;

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