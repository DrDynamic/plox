<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;

class BooleanValue extends BaseValue
{
    #[\Override] public static function getType(): LoxType
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