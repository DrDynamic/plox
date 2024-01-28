<?php

namespace src\Interpreter\Runtime\Native\Functions;

use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\LoxType;
use src\Interpreter\Runtime\Values\BaseValue;
use src\Interpreter\Runtime\Values\CallableValue;
use src\Interpreter\Runtime\Values\StringValue;

abstract class NativeFunction extends BaseValue implements CallableValue
{
    public function getType(): LoxType
    {
        return LoxType::Callable;
    }


    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        if ($toType == LoxType::String) {
            return new StringValue("<native fn>");
        }
        return parent::cast($toType, $cause); // TODO: Change the autogenerated stub
    }


}