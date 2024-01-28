<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\Errors\DivisionByZeroError;
use src\Interpreter\Runtime\Errors\InvalidCastError;
use src\Interpreter\Runtime\Errors\RuntimeError;
use src\Interpreter\Runtime\LoxType;
use src\Scaner\Token;
use src\Scaner\TokenType;

abstract class BaseValue implements Value
{
    abstract public function getType(): LoxType;

    /**
     * @param LoxType $toType The type to cast to
     * @param Expression $cause The Statement or Expression, that caused the cast
     * @return BaseValue
     * @throws InvalidCastError
     */
    public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        if ($this->getType() == $toType) return $this;

        $ownType  = $this->getType()->value;
        $destType = $toType->value;

        throw new InvalidCastError($cause->tokenStart, "Invalid cast.\n Can not cast {$ownType} to {$destType}");
    }

    public function compare(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        $ownValue   = $this->cast(LoxType::Number, $cause);
        $otherValue = $value->cast(LoxType::Number, $cause);

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
        $ownValue   = $this->cast(LoxType::Number, $cause);
        $otherValue = $value->cast(LoxType::Number, $cause);

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