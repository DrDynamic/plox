<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;

class BooleanValue extends BaseValue
{
    #[\Override] public static function getType(): ValueType
    {
        return ValueType::Boolean;
    }

    public function __construct(
        public readonly bool $value
    )
    {
    }

    #[\Override] public function cast(ValueType $toType, Statement|Expression $cause): BaseValue
    {
        switch ($toType) {
            case ValueType::Boolean:
                return $this;
            case ValueType::Number:
                return new NumberValue($this->value ? 1.0 : 0.0);
            case ValueType::String:
                return new StringValue($this->value ? "true" : "false");
        }
        return parent::cast($toType, $cause);
    }

    #[\Override] public function compare(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        $number = $this->cast(ValueType::Number, $cause);
        $value  = $value->cast(ValueType::Number, $cause);

        return $number->compare($value, $operatorToken, $cause);
    }

    #[\Override] public function calc(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        throw new InvalidMathError($operatorToken, "Arithmetic actions with boolean are not allowed");
    }


}