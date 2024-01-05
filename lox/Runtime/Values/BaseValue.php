<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Errors\DivisionByZeroError;
use Lox\Runtime\Errors\InvalidCastError;
use Lox\Runtime\Errors\RuntimeError;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

abstract class BaseValue implements Value
{
    abstract public static function getType(): ValueType;

    /**
     * @param ValueType $toType The type to cast to
     * @param Expression $cause The Statement or Expression, that caused the cast
     * @return BaseValue
     * @throws InvalidCastError
     */
    public function cast(ValueType $toType, Statement|Expression $cause): BaseValue
    {
        $ownType  = static::getType()->value;
        $destType = $toType->value;

        throw new InvalidCastError($cause->tokenStart, "Invalid cast.\n Can not cast {$ownType} to {$destType}");
    }

    public function compare(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        $ownValue   = $this->cast(ValueType::Number, $cause);
        $otherValue = $value->cast(ValueType::Number, $cause);

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

    public function calc(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        $ownValue   = $this->cast(ValueType::Number, $cause);
        $otherValue = $value->cast(ValueType::Number, $cause);

        switch ($operatorToken->type) {
            case TokenType::PLUS:
                return new NumberValue($ownValue->value + $otherValue->value);
            case TokenType::MINUS:
                return new NumberValue($ownValue->value - $otherValue->value);
            case TokenType::STAR:
                return new NumberValue($ownValue->value * $otherValue->value);
            case TokenType::SLASH:
                if ($this->value == 0 || $ownValue->value == 0) {
                    throw new DivisionByZeroError($operatorToken, "Division by zero.");
                }
                return new NumberValue($ownValue->value / $otherValue->value);
        }

        throw new RuntimeError($operatorToken, "Unknown calculation operator '$operatorToken->lexeme'");
    }
}