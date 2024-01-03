<?php

namespace Lox\Runtime\Values;

use Lox\Runtime\Errors\DivisionByZeroError;
use Lox\Runtime\Errors\InvalidCastError;
use Lox\Runtime\Errors\RuntimeError;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

abstract class Value
{
    abstract public static function getType(): ValueType;

    public function cast(ValueType $toType): Value
    {
        $ownType  = static::getType()->value;
        $destType = $toType->value;
        throw new InvalidCastError(null, "Invalid cast.\n Can not cast {$ownType} to {$destType}");
    }

    public function compare(Value $value, Token $operatorToken): Value
    {
        $ownValue   = $this->cast(ValueType::Number);
        $otherValue = $value->cast(ValueType::Number);

        switch ($operatorToken->type) {
            case TokenType::EQUAL_EQUAL:
                return new BooleanValue($ownValue->value === $otherValue->value);
            case TokenType::BANG_EQUAL:
                return new BooleanValue($ownValue->value !== $otherValue->value);
            case TokenType::GREATER:
                return new BooleanValue($ownValue->value > $otherValue->value);
            case TokenType::GREATER_EQUAL:
                return new BooleanValue($ownValue->value >= $otherValue->value);
            case TokenType::LESS:
                return new BooleanValue($ownValue->value < $otherValue->value);
            case TokenType::LESS_EQUAL:
                return new BooleanValue($ownValue->value <= $otherValue->value);
        }

        throw new RuntimeError($operatorToken, "Unknown compare operator '$operatorToken->lexeme'");
    }

    public function calc(Value $value, Token $operatorToken): Value
    {
        $ownValue   = $this->cast(ValueType::Number);
        $otherValue = $value->cast(ValueType::Number);

        if ($this->value == 0 || $ownValue->value == 0) {
            throw new DivisionByZeroError($operatorToken, "Division by zero.");
        }

        switch ($operatorToken->type) {
            case TokenType::PLUS:
                return new NumberValue($ownValue->value + $otherValue->value);
            case TokenType::MINUS:
                return new NumberValue($ownValue->value - $otherValue->value);
            case TokenType::STAR:
                return new NumberValue($ownValue->value * $otherValue->value);
            case TokenType::SLASH:
                return new NumberValue($ownValue->value / $otherValue->value);
        }

        throw new RuntimeError($operatorToken, "Unknown calculation operator '$operatorToken->lexeme'");
    }
}