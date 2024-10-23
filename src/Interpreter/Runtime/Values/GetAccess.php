<?php

namespace src\Interpreter\Runtime\Values;

use src\Scaner\Token;

interface GetAccess
{
    public function get(Token $name);
}