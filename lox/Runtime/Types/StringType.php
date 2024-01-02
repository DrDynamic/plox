<?php

namespace Lox\Runtime\Types;

use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

class StringType extends Type
{
    #[\Override] public static function getType(): LoxType
    {
        return LoxType::String;
    }

    public function __construct(
        public readonly string $value
    )
    {
    }

    #[\Override] public function cast(LoxType $toType): Type
    {
        switch ($toType) {
            case LoxType::Boolean:
                return new BooleanType($this->value !== "");
            case LoxType::Number:
                return new NumberType(mb_strlen($this->value));
            case LoxType::String:
                return $this;
        }
        return parent::cast($toType);
    }

    #[\Override] public function calc(Type $value, Token $operatorToken): Type
    {
        switch ($operatorToken->type) {
            case TokenType::PLUS:
                $value = $value->cast(LoxType::String);
                return new StringType($this->value.$value->value);
        }
        throw new InvalidMathError($operatorToken, "Arithmetic actions with string are not allowed (except concatenation '+' operator)");
    }


}