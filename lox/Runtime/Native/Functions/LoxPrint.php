<?php

namespace Lox\Runtime\Native\Functions;

use Lox\AST\Expressions\Expression;
use Lox\Runtime\Values\CallableValue;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\BaseValue;
use Lox\Runtime\Values\ValueType;

class LoxPrint extends BaseValue implements CallableValue
{

    #[\Override] public function arity(): int
    {
        return 0;
    }

    #[\Override] public function call(array $arguments): BaseValue
    {
        echo $arguments[0]->cast(ValueType::String)->value."\n";
        return dependency(NilValue::class);
    }

    #[\Override] public static function getType(): ValueType
    {
        return ValueType::Callable;
    }
}