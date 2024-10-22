<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\ClassExpression;
use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\LoxType;

class ClassValue extends BaseValue implements CallableValue
{

    public function __construct(
        private readonly ClassExpression $declaration,
    )
    {
    }

    public function getType(): LoxType
    {
        return LoxType::Klass;
    }

    public function arity(): int
    {
        return 0;
    }

    public function call(array $arguments, Expression|Statement $cause): Value
    {
        return new NilValue();
    }
}