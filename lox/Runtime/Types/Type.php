<?php

namespace Lox\Runtime\Types;

use Lox\Runtime\Errors\InvalidCastError;
use Lox\Runtime\Errors\RuntimeError;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

abstract class Type
{
    abstract public static function getType(): LoxType;

    public function cast(LoxType $toType): Type
    {
        $ownType  = static::getType()->value;
        $destType = $toType->value;
        throw new InvalidCastError(null, "Invalid cast.\n Can not cast {$ownType} to {$destType}");
    }

    public function compare(Type $value, Token $operatorToken): Type
    {
        $ownValue   = $this->cast(LoxType::Number);
        $otherValue = $value->cast(LoxType::Number);

        switch ($operatorToken->type) {
            case TokenType::EQUAL_EQUAL:
                return new BooleanType($ownValue->value === $otherValue->value);
            case TokenType::BANG_EQUAL:
                return new BooleanType($ownValue->value !== $otherValue->value);
            case TokenType::GREATER:
                return new BooleanType($ownValue->value > $otherValue->value);
            case TokenType::GREATER_EQUAL:
                return new BooleanType($ownValue->value >= $otherValue->value);
            case TokenType::LESS:
                return new BooleanType($ownValue->value < $otherValue->value);
            case TokenType::LESS_EQUAL:
                return new BooleanType($ownValue->value <= $otherValue->value);
        }

        throw new RuntimeError($operatorToken, "Unknown compare operator '$operatorToken->lexeme'");
    }

    public function calc(Type $value, Token $operatorToken): Type
    {
        $ownValue   = $this->cast(LoxType::Number);
        $otherValue = $value->cast(LoxType::Number);

        switch ($operatorToken->type) {
            case TokenType::PLUS:
                return new NumberType($ownValue->value + $otherValue->value);
            case TokenType::MINUS:
                return new NumberType($ownValue->value - $otherValue->value);
            case TokenType::STAR:
                return new NumberType($ownValue->value * $otherValue->value);
            case TokenType::SLASH:
                return new NumberType($ownValue->value / $otherValue->value);
        }

        throw new RuntimeError($operatorToken, "Unknown calculation operator '$operatorToken->lexeme'");
    }
}