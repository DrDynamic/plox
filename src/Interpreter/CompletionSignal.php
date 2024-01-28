<?php

namespace src\Interpreter;

use src\AST\Statements\CompletionStatement;
use src\Interpreter\Runtime\Errors\RuntimeError;

class CompletionSignal extends RuntimeError
{
    public function __construct(
        public readonly CompletionStatement $statement
    )
    {
        $lexeme = $statement->operator->lexeme;
        parent::__construct($statement->operator, "Unexpected '$lexeme' outside of loop.");
    }
}