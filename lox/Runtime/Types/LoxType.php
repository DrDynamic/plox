<?php

namespace Lox\Runtime\Types;

enum LoxType: string
{
    case NIL = "nil";
    case Boolean = "boolean";
    case Number = "number";
    case String = "string";
}