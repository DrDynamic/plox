<?php

namespace Lox\Runtime\Values;

interface CallableValue extends Value
{
    /**
     * Number of arguments
     * @return int
     */
    public function arity(): int;

    /**
     * Execute function
     * @param array<BaseValue> $arguments
     * @return BaseValue
     */
    public function call(array $arguments): BaseValue;

}