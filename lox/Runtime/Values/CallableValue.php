<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;

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