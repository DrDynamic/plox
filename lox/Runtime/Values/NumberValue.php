<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\Statement;

class NumberValue extends BaseValue
{
    #[\Override] public static function getType(): ValueType
    {
        return ValueType::Number;
    }

    public function __construct(
        public readonly float $value
    )
    {
    }

    #[\Override] public function cast(ValueType $toType, Statement|Expression $cause): BaseValue
    {
        switch ($toType) {
            case ValueType::Boolean:
                return new BooleanValue($this->value !== 0.0);
            case ValueType::Number:
                return $this;
            case ValueType::String:
                return new StringValue("$this->value");
        }
        return parent::cast($toType, $cause);
    }

}