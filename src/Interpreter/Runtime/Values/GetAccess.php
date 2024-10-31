<?php

namespace src\Interpreter\Runtime\Values;

use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;

interface GetAccess
{
    public function get(Token $name);
}