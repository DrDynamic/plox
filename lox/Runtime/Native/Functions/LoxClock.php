<?php

namespace Lox\Runtime\Native\Functions;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\Value;

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