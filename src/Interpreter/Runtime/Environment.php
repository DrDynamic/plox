<?php

namespace src\Interpreter\Runtime;

use src\Interpreter\Runtime\Errors\InvalidDeclarationError;
use src\Interpreter\Runtime\Errors\UndefinedVariableError;
use src\Interpreter\Runtime\Native\Natives;
use src\Interpreter\Runtime\Values\Value;
use src\Scaner\Token;
use src\Services\Dependency\Attributes\Instance;

#[Instance]
class Environment
{
    protected $values = [];

    public function __construct(
        public readonly Environment|null $enclosing = null,
        private readonly Natives|null    $natives = null,
    )
    {
        $this->initValues();
    }

    private function initValues(): void
    {
        if ($this->natives != null) {
            $this->values = $this->natives->nativeFunctions;
        }
    }

    public function reset(bool $recursive = false): self
    {
        $this->initValues();
        return $this;
    }

    public function ancestor(int $distance)
    {
        $env = $this;
        for ($i = 0; $i < $distance; $i++) {
            $env = $env->enclosing;
        }
        return $env;
    }

    public function defineOrReplace(string $name, Value $value)
    {
        $this->values[$name] = $value;
    }

    public function defineOrFail(Token $name, Value $value): void
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

    public function assignAt(int $distance, Token $name, Value $value)
    {
        $this->ancestor($distance)->values[$name->lexeme] = $value;
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

    public function getAt(mixed $distance, Token|string $name)
    {
        $lexeme = is_string($name) ? $name : $name->lexeme;
        return $this->ancestor($distance)->values[$lexeme];
    }


}