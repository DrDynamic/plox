<?php

namespace src\Interpreter;

use src\AST\Statements\ReturnStatement;
use src\Interpreter\Runtime\Errors\RuntimeError;
use src\Interpreter\Runtime\Values\Value;

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