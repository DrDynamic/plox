<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

class StringValue extends BaseValue
{
    #[\Override] public static function getType(): ValueType
    {
        return ValueType::String;
    }

    public function __construct(
        public readonly string $value
    )
    {
    }

    #[\Override] public function cast(ValueType $toType, Statement|Expression $cause): BaseValue
    {
        switch ($toType) {
            case ValueType::Boolean:
                return new BooleanValue($this->value !== "");
            case ValueType::Number:
                return new NumberValue(mb_strlen($this->value));
            case ValueType::String:
                return $this;
        }
        return parent::cast($toType, $cause);
    }

    #[\Override] public function calc(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        switch ($operatorToken->type) {
            case TokenType::PLUS:
                $value = $value->cast(ValueType::String, $cause);
                return new StringValue($this->value.$value->value);
        }
        throw new InvalidMathError($operatorToken, "Arithmetic actions with string are not allowed (except concatenation '+' operator)");
    }


}