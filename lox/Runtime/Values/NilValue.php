<?php

namespace Lox\Runtime\Values;

use App\Attributes\Singleton;
use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Errors\InvalidMathError;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

#[Singleton]
class NilValue extends BaseValue
{
    #[\Override] public static function getType(): LoxType
    {
        return LoxType::NIL;
    }

    public function __construct(
        public readonly null $value = null
    )
    {
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        switch ($toType) {
            case LoxType::Boolean:
                return new BooleanValue(false);
            case LoxType::String:
                return new StringValue('nil');
        }
        return parent::cast($toType, $cause); // TODO: Change the autogenerated stub
    }

    #[\Override] public function compare(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        switch ($operatorToken->type) {
            case TokenType::EQUAL_EQUAL:
                if ($value::getType() == LoxType::NIL) return new BooleanValue(true);
            case TokenType::BANG_EQUAL:
                if ($value::getType() == LoxType::NIL) return new BooleanValue(false);
            case TokenType::GREATER:
            case TokenType::GREATER_EQUAL:
                return new BooleanValue(true);
            case TokenType::LESS:
            case TokenType::LESS_EQUAL:
                return new BooleanValue(false);
        }

        return parent::compare($value, $operatorToken, $cause);
    }

    #[\Override] public function calc(Value $value, Token $operatorToken, Statement|Expression $cause): BaseValue
    {
        throw new InvalidMathError($operatorToken, "Arithmetic actions with nil are not allowed");
    }


}