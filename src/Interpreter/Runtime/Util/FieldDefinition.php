<?php

namespace src\Interpreter\Runtime\Util;

use src\Interpreter\Runtime\Values\Value;
use src\Resolver\LoxClassPropertyVisibility;

class FieldDefinition
{
    public function __construct(
        public LoxClassPropertyVisibility $visibility,
        public Value                      $value,
    )
    {
    }

    public function __clone(): void
    {
        $this->value = clone $this->value;
    }

}