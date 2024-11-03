<?php

namespace src\Interpreter\Runtime\Values;

use src\Interpreter\Runtime\ExecutionContext;
use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;

interface GetAccess
{
    public function getOrFail(Token $name, ExecutionContext $executionContext);
}