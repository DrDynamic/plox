<?php

namespace Lox\Runtime;

use App\Attributes\Instance;
use Lox\Runtime\Errors\InvalidDeclarationError;
use Lox\Runtime\Errors\UndefinedVariableError;
use Lox\Runtime\Values\Value;
use Lox\Scan\Token;

#[Instance]
class Environment
{
    private $values = [];

    public function define(Token $name, Value $value): void
    {
        if (empty($this->values[$name->lexeme])) {
            $this->values[$name->lexeme] = $value;
        } else {
            throw new InvalidDeclarationError($name, "Cannot redeclare variable '$name->lexeme'.");
        }
    }

    public function has(Token $name): bool
    {
        return !empty($this->values[$name->lexeme]);
    }

    public function get(Token $name): Value
    {
        if (!empty($this->values[$name->lexeme])) {
            return $this->values[$name->lexeme];
        }
        throw new UndefinedVariableError($name, "Undefined variable '$name->lexeme'.");
    }
}