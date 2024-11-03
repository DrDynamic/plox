<?php

namespace src\Interpreter\Runtime\Values;

use src\Interpreter\Runtime\ExecutionContext;
use src\Scaner\Token;

interface SetAccess
{
    public function setOrFail(Token $name, Value $value, ExecutionContext $executionContext);
}