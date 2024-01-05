<?php

namespace Lox\Runtime\Native\Functions;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Values\BaseValue;
use Lox\Runtime\Values\CallableValue;
use Lox\Runtime\Values\LoxType;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\Value;

class LoxPrint extends BaseValue implements CallableValue
{

    #[\Override] public function getType(): LoxType
    {
        return LoxType::Callable;
    }

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