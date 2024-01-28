<?php

namespace src\Interpreter\Runtime\Native\Functions;

use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\LoxType;
use src\Interpreter\Runtime\Values\NilValue;
use src\Interpreter\Runtime\Values\Value;

class LoxPrint extends NativeFunction
{
    #[\Override] public function arity(): int
    {
        return 0;
    }

    #[\Override] public function call(array $arguments, Statement|Expression $cause): Value
    {
        echo $arguments[0]->cast(LoxType::String, $cause)->value."\n";
        return dependency(NilValue::class);
    }
}