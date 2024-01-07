<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

class StringValue extends BaseValue
{
    #[\Override] public function getType(): LoxType
    {
        return LoxType::String;
    }

    public function __construct(
        public readonly string $value
    )
    {
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {

        switch ($toType) {
            case LoxType::Boolean:
                return new BooleanValue($this->value !== "");
            case LoxType::Number:
                return new NumberValue(mb_strlen($this->value));
            case LoxType::String:
                return $this;
        }
        return parent::cast($toType, $cause);
    }

    #[\Override] public function calc(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        switch ($operatorToken->type) {
            case TokenType::PLUS:
                $value = $value->cast(LoxType::String, $cause);
                return new StringValue($this->value.$value->value);
        }
        throw new InvalidMathError($operatorToken, "Arithmetic actions with string are not allowed (except concatenation '+' operator)");
    }


}