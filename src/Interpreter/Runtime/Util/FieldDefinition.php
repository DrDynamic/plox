<?php

namespace src\Interpreter\Runtime\Util;

use src\Interpreter\Runtime\Values\Value;
use src\Resolver\LoxClassPropertyVisibility;

class FieldDefinition
{
    public function __construct(
        public LoxClassPropertyVisibility $visibility,
        public Value $value,
    )
    {
    }

}