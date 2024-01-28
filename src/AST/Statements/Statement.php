<?php

namespace src\AST\Statements;

use src\AST\StatementVisitor;
use src\Scaner\Token;

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