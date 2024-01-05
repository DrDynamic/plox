<?php

namespace Lox\Runtime\Values;

enum ValueType: string
{
    case NIL = "nil";
    case Boolean = "boolean";
    case Number = "number";
    case String = "string";
    case Callable = "callable";
}