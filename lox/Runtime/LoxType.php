<?php

namespace Lox\Runtime;

enum LoxType: string
{
    case NIL = "nil";
    case Boolean = "boolean";
    case Number = "number";
    case String = "string";
    case Callable = "callable";
}