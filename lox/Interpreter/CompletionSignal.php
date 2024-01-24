<?php

namespace Lox\Interpreter;

use Lox\AST\Statements\CompletionStatement;
use Lox\Runtime\Errors\RuntimeError;

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