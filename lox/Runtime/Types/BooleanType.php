<?php

namespace Lox\Runtime\Types;

use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;

class BooleanType extends Type
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

    #[\Override] public function cast(LoxType $toType): Type
    {
        switch ($toType) {
            case LoxType::Boolean:
                return $this;
            case LoxType::Number:
                return new NumberType($this->value ? 1.0 : 0.0);
            case LoxType::String:
                return new StringType($this->value ? "true" : "false");
        }
        return parent::cast($toType);
    }

    #[\Override] public function compare(Type $value, Token $operatorToken): Type
    {
        $number = $this->cast(LoxType::Number);
        $value  = $value->cast(LoxType::Number);

        return $number->compare($value, $operatorToken);
    }

    #[\Override] public function calc(Type $value, Token $operatorToken): Type
    {
        throw new InvalidMathError($operatorToken, "Arithmetic actions with boolean are not allowed");
    }


}