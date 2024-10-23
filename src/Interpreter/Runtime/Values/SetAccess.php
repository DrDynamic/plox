<?php

namespace src\Interpreter\Runtime\Values;

use src\Scaner\Token;

interface SetAccess
{
    public function set(Token $name, Value $value);
}