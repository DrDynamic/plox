<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\Errors\InvalidCastError;
use src\Interpreter\Runtime\LoxType;
use src\Scaner\Token;

interface Value
{
    public function getType(): LoxType;

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