<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Errors\InvalidCastError;
use Lox\Scan\Token;

interface Value
{
    public static function getType(): LoxType;

    /**
     * @param LoxType $toType The type to cast to
     * @param Expression $cause The Statement or Expression, that caused the cast
     * @return Value
     * @throws InvalidCastError
     */
    public function cast(LoxType $toType, Statement|Expression $cause): Value;

    public function compare(Value $value, Token $operatorToken, Statement|Expression $cause): Value;

    public function calc(Value $value, Token $operatorToken, Statement|Expression $cause): Value;
}