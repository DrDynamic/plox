<?php

namespace Lox\Runtime\Native\Functions;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\LoxType;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\Value;

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