<?php

namespace src\Resolver;

enum LoxFunctionType
{
    case NONE;
    case FUNCTION;
    case CONSTRUCTOR;
    case METHOD;
}
