<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;
use Lox\Runtime\LoxType;

class NumberValue extends BaseValue
{
    #[\Override] public function getType(): LoxType
    {
        return LoxType::Number;
    }

    public function __construct(
        public readonly float $value
    )
    {
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        switch ($toType) {
            case LoxType::Boolean:
                return new BooleanValue($this->value !== 0.0);
            case LoxType::Number:
                return $this;
            case LoxType::String:
                return new StringValue("$this->value");
        }
        return parent::cast($toType, $cause);
    }

}