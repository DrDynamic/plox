<?php

namespace Lox\Interpreter;

use Lox\AST\Statements\ReturnStatement;
use Lox\Runtime\Errors\RuntimeError;
use Lox\Runtime\Values\Value;

class ReturnSignal extends RuntimeError
{
    public function __construct(
        public readonly ReturnStatement $statement,
        public readonly Value           $value
    )
    {
        parent::__construct($statement->keyword, "Unexpected 'return' statement.");
    }
}