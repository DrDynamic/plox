<?php

namespace Lox\AST\Statements;

use Lox\AST\StatementVisitor;
use Lox\Scan\Token;

abstract class Statement implements \JsonSerializable
{
    public function __construct(
        public readonly Token $tokenStart,
        public readonly Token $tokenEnd,
    )
    {
    }

    abstract function accept(StatementVisitor $visitor);
}