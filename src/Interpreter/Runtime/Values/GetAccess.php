<?php

namespace src\Interpreter\Runtime\Values;

use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;

interface GetAccess
{
    public function getOrFail(Token $name);
    public function get(Token $name);
}