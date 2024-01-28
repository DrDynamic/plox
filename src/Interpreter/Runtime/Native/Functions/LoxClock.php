<?php

namespace src\Interpreter\Runtime\Native\Functions;

use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\Values\NumberValue;
use src\Interpreter\Runtime\Values\Value;

class LoxClock extends NativeFunction
{
    #[\Override] public function arity(): int
    {
        return 0;
    }

    #[\Override] public function call(array $arguments, Statement|Expression $cause): Value
    {
        return new NumberValue(microtime(true));
    }
}