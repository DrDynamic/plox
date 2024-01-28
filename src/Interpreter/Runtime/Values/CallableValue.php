<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;

interface CallableValue extends Value
{
    /**
     * Number of arguments
     * @return int
     */
    public function arity(): int;

    /**
     * Execute function
     * @param array<BaseValue> $arguments
     * @return BaseValue
     */
    public function call(array $arguments, Statement|Expression $cause): Value;

}