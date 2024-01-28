<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\Errors\InvalidMathError;
use src\Interpreter\Runtime\LoxType;
use src\Scaner\Token;

class BooleanValue extends BaseValue
{
    #[\Override] public function getType(): LoxType
    {
        return LoxType::Boolean;
    }

    public function __construct(
        public readonly bool $value
    )
    {
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        switch ($toType) {
            case LoxType::Boolean:
                return $this;
            case LoxType::Number:
                return new NumberValue($this->value ? 1.0 : 0.0);
            case LoxType::String:
                return new StringValue($this->value ? "true" : "false");
        }
        return parent::cast($toType, $cause);
    }

    #[\Override] public function compare(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        $number = $this->cast(LoxType::Number, $cause);
        $value  = $value->cast(LoxType::Number, $cause);

        return $number->compare($value, $operatorToken, $cause);
    }

    #[\Override] public function calc(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        throw new InvalidMathError($operatorToken, "Arithmetic actions with boolean are not allowed");
    }


}