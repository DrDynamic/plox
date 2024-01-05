<?php

namespace Lox\Runtime;

use App\Attributes\Instance;
use Lox\Runtime\Errors\InvalidDeclarationError;
use Lox\Runtime\Errors\UndefinedVariableError;
use Lox\Runtime\Native\Natives;
use Lox\Runtime\Values\Value;
use Lox\Scan\Token;

#[Instance]
class Environment
{
    private $values = [];

    public function __construct(
        public readonly Environment|null $enclosing = null,
        private readonly Natives|null    $natives = null,
    )
    {
        $this->initValues();
    }

    private function initValues()
    {
        if ($this->natives != null) {
            $this->values = $this->natives->nativeFunctions;
        }
    }

    public function reset(bool $recursive = false)
    {
        $this->initValues();
        return $this;
    }

    public function define(Token $name, Value $value): void
    {
        if (!$this->has($name)) {
            $this->values[$name->lexeme] = $value;
        } else {
            throw new InvalidDeclarationError($name, "Cannot redeclare variable '$name->lexeme'.");
        }
    }

    public function delete(Token $name)
    {
        if ($this->has($name)) {
            unset($this->values[$name->lexeme]);
        } else {
            throw new InvalidDeclarationError($name, "Cannot delete undefined variable '$name->lexeme'.");
        }
    }

    public function assign(Token $name, Value $value): void
    {
        if ($this->has($name)) {
            $this->values[$name->lexeme] = $value;
        } else if ($this->enclosing != null) {
            $this->enclosing->assign($name, $value);
        } else {
            throw new UndefinedVariableError($name, "Undefined variable '$name->lexeme'.");
        }
    }

    public function has(Token $name): bool
    {
        return !empty($this->values[$name->lexeme]);
    }

    public function get(Token $name): Value
    {
        if ($this->has($name)) {
            return $this->values[$name->lexeme];
        } else if ($this->enclosing != null) {
            return $this->enclosing->get($name);
        }
        throw new UndefinedVariableError($name, "Undefined variable '$name->lexeme'.");
    }


}