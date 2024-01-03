<?php

namespace Lox\Runtime\Values;

use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;

class BooleanValue extends Value
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

    #[\Override] public function cast(ValueType $toType): Value
    {
        switch ($toType) {
            case ValueType::Boolean:
                return $this;
            case ValueType::Number:
                return new NumberValue($this->value ? 1.0 : 0.0);
            case ValueType::String:
                return new StringValue($this->value ? "true" : "false");
        }
        return parent::cast($toType);
    }

    #[\Override] public function compare(Value $value, Token $operatorToken): Value
    {
        $number = $this->cast(ValueType::Number);
        $value  = $value->cast(ValueType::Number);

        return $number->compare($value, $operatorToken);
    }

    #[\Override] public function calc(Value $value, Token $operatorToken): Value
    {
        throw new InvalidMathError($operatorToken, "Arithmetic actions with boolean are not allowed");
    }


}